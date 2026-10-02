<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EwalletTransaction;
use App\Support\EwalletService;
use App\Support\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook notifikasi Midtrans.
 *
 * Dipanggil oleh Midtrans (bukan oleh anggota), jadi:
 * - tidak memakai session/auth API
 * - signature_key diverifikasi (sha512 order_id+status_code+gross_amount+server_key)
 * - idempoten: notifikasi yang sama tidak menambah saldo dua kali
 *
 * URL: POST /api/payment/midtrans/notify
 */
class PaymentNotificationController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly EwalletService $ewallet,
    ) {
    }

    public function midtrans(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->midtrans->isConfigured()) {
            return response()->json(['message' => 'Payment gateway belum dikonfigurasi.'], 503);
        }

        if (! $this->midtrans->verifyNotification($payload)) {
            Log::warning('Notifikasi Midtrans ditolak: signature tidak cocok.', $payload);

            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');

        $trx = EwalletTransaction::where('ref_id', $orderId)
            ->where('tipe', 'topup')
            ->where('gateway', 'midtrans')
            ->first();

        if (! $trx) {
            // Order bukan topup e-wallet (mis. transaksi PPOB) → biarkan,
            // jangan dibatalkan karena Midtrans mengirim notifikasi otomatis.
            return response()->json(['message' => 'Order tidak dikenal, notifikasi diabaikan.'], 200);
        }

        if ($this->midtrans->isPaid($payload)) {
            $this->ewallet->kreditkanTopup($trx, $payload['payment_type'] ?? $payload['transaction_status']);

            return response()->json(['message' => 'Saldo E-Wallet berhasil bertambah.'], 200);
        }

        if ($this->midtrans->isFailed($payload)) {
            $this->ewallet->gagalkanTopup($trx, 'Status bank: '.($payload['transaction_status'] ?? '-'));

            return response()->json(['message' => 'Transaksi gagal/kedaluwarsa.'], 200);
        }

        // 'pending' → biarkan menunggu, anggota belum menyelesaikan pembayaran
        return response()->json(['message' => 'Notifikasi diterima, transaksi masih berjalan.'], 200);
    }
}
