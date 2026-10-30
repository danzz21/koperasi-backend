<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Simpanan;
use App\Services\AdminApprovalService;
use App\Services\AdminSimpananService;

class AdminSimpananController extends Controller
{
    public function __construct(
        private readonly AdminApprovalService $approvals,
        private readonly AdminSimpananService $simpanan,
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->simpanan->listing()]);
    }

    public function pending()
    {
        return response()->json(['data' => $this->simpanan->pending()]);
    }

    public function approve($jenis, $id)
    {
        $row = Simpanan::findOrFail($id);
        abort_unless($row->jenis === $jenis, 404);
        abort_unless($this->approvals->approveSavings($row, $jenis), 409, 'Setoran sudah diproses atau pengunduran diri sedang diproses.');

        return response()->json(['message' => 'Disetujui']);
    }

    public function reject($jenis, $id)
    {
        $row = Simpanan::findOrFail($id);
        abort_unless($row->jenis === $jenis, 404);
        abort_unless($this->approvals->rejectSavings($row, $jenis), 409, 'Setoran sudah diproses.');

        return response()->json(['message' => 'Ditolak']);
    }
}
