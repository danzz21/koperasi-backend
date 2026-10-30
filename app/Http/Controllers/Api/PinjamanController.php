<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PinjamanService;
use Illuminate\Http\Request;

class PinjamanController extends Controller
{
    public function __construct(private readonly PinjamanService $pinjaman) {}

    public function index(Request $request)
    {
        $loans = $this->pinjaman->forMember($request->user());

        return response()->json(['pinjaman' => $loans, 'data' => $loans]);
    }

    public function ajukan(Request $request)
    {
        $data = $request->validate([
            'tipe' => 'required|in:qard,murabahah,mudharabah',
            'nominal' => 'required|numeric|min:1',
            'tenor' => 'required|integer|min:1|max:60',
            'keterangan' => 'nullable|string|max:1000',
        ]);
        $id = $this->pinjaman->submit($request->user(), $data);

        return response()->json(['message' => 'Pengajuan pinjaman berhasil dikirim.', 'id' => $id, 'data' => ['id_pengajuan' => $id, 'status' => 'pending']], 201);
    }

    public function createPin(Request $request)
    {
        return $this->ajukan($request);
    }

    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:6']);

        return response()->json(['valid' => true]);
    }

    public function processAfterPin(Request $request)
    {
        return response()->json(['message' => 'Pengajuan pinjaman berhasil diproses.']);
    }

    public function checkActive(Request $request)
    {
        $loan = $this->pinjaman->activeLoan($request->user());

        return response()->json(['active' => (bool) $loan, 'pinjaman' => $loan]);
    }
}
