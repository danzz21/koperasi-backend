<?php

namespace App\Services;

use App\Models\EwalletTransaction;
use Illuminate\Support\Facades\Log;

class MidtransNotificationService
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly EwalletService $ewallet,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{status: int, message: string}
     */
    public function handle(array $payload): array
    {
        if (! $this->midtrans->isConfigured()) {
            return ['status' => 503, 'message' => 'Payment gateway belum dikonfigurasi.'];
        }

        if (! $this->midtrans->verifyNotification($payload)) {
            Log::warning('Notifikasi Midtrans ditolak: signature tidak cocok.', $payload);

            return ['status' => 403, 'message' => 'Signature tidak valid.'];
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $transaction = EwalletTransaction::query()
            ->where('ref_id', $orderId)
            ->where('tipe', 'topup')
            ->where('gateway', 'midtrans')
            ->first();

        if (! $transaction) {
            return ['status' => 200, 'message' => 'Order tidak dikenal, notifikasi diabaikan.'];
        }

        if ($this->midtrans->isPaid($payload)) {
            $this->ewallet->kreditkanTopup(
                $transaction,
                $payload['payment_type'] ?? $payload['transaction_status'] ?? null,
            );

            return ['status' => 200, 'message' => 'Saldo E-Wallet berhasil bertambah.'];
        }

        if ($this->midtrans->isFailed($payload)) {
            $this->ewallet->gagalkanTopup($transaction, 'Status bank: '.($payload['transaction_status'] ?? '-'));

            return ['status' => 200, 'message' => 'Transaksi gagal/kedaluwarsa.'];
        }

        return ['status' => 200, 'message' => 'Notifikasi diterima, transaksi masih berjalan.'];
    }
}
