<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminApprovalService;
use App\Services\AdminMemberService;
use Illuminate\Http\Request;

class AdminAnggotaController extends Controller
{
    public function __construct(
        private readonly AdminMemberService $members,
        private readonly AdminApprovalService $approvals,
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->members->listing()]);
    }

    public function store(Request $request)
    {
        return response()->json(['data' => $request->all()], 201);
    }

    public function show($id)
    {
        return response()->json(['data' => $this->members->find($id)]);
    }

    public function update(Request $request, $id)
    {
        $u = $this->members->update($id, $request->only(['nama_lengkap', 'status', 'nomor_hp']));

        return response()->json(['data' => $u]);
    }

    public function destroy($id)
    {
        $this->members->delete($id);

        return response()->noContent();
    }

    public function verify($id)
    {
        $u = $this->members->find($id);
        abort_unless($this->approvals->approveMember($u), 409, 'Anggota sudah diproses.');

        return response()->json(['message' => 'Anggota diverifikasi']);
    }

    public function reject($id)
    {
        $u = $this->members->find($id);
        abort_unless($this->approvals->rejectMember($u), 409, 'Anggota sudah diproses.');

        return response()->json(['message' => 'Anggota ditolak']);
    }

    public function pending()
    {
        return response()->json(['data' => $this->members->pending()]);
    }
}
