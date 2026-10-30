<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MidtransNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function __construct(private readonly MidtransNotificationService $notifications) {}

    public function midtrans(Request $request): JsonResponse
    {
        $result = $this->notifications->handle($request->all());

        return response()->json(['message' => $result['message']], $result['status']);
    }
}
