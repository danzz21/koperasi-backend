<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\AdminApprovalService;
use App\Services\AdminPaymentService;

class AdminPaymentController extends Controller
{
    public function __construct(
        private readonly AdminApprovalService $approvals,
        private readonly AdminPaymentService $payments,
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->payments->listing()]);
    }

    public function pendingCicilan()
    {
        return response()->json(['data' => $this->payments->pendingInstallments()]);
    }

    public function verifikasi($id)
    {
        $payment = Pembayaran::findOrFail($id);
        abort_unless($this->approvals->approvePayment($payment), 409, 'Pembayaran sudah diproses.');

        return response()->json(['message' => 'Pembayaran diverifikasi']);
    }

    public function tolak($id)
    {
        $payment = Pembayaran::findOrFail($id);
        abort_unless($this->approvals->rejectPayment($payment), 409, 'Pembayaran sudah diproses.');

        return response()->json(['message' => 'Pembayaran ditolak']);
    }
}
