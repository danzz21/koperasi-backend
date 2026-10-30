<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pinjaman;
use App\Services\AdminApprovalService;
use App\Services\AdminPinjamanService;

class AdminPinjamanController extends Controller
{
    public function __construct(
        private readonly AdminApprovalService $approvals,
        private readonly AdminPinjamanService $pinjaman,
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->pinjaman->listing()]);
    }

    public function pending()
    {
        return response()->json(['data' => $this->pinjaman->pending()]);
    }

    public function approve($jenis, $id)
    {
        $row = Pinjaman::findOrFail($id);
        abort_unless($this->approvals->approveLoan($row, $jenis), 409, 'Pengajuan pinjaman sudah diproses.');

        return response()->json(['message' => 'Disetujui']);
    }

    public function reject($jenis, $id)
    {
        $row = Pinjaman::findOrFail($id);
        abort_unless($row->status === 'pending', 409, 'Pengajuan pinjaman sudah diproses.');
        $this->approvals->rejectLoan($row, $jenis);

        return response()->json(['message' => 'Ditolak']);
    }

    public function disburse($jenis, $id)
    {
        $loan = Pinjaman::findOrFail($id);
        abort_unless($this->approvals->disburseLoan($loan), 409, 'Pinjaman belum disetujui atau sudah dicairkan.');

        return response()->json(['message' => 'Pencairan pinjaman dicatat.']);
    }
}
