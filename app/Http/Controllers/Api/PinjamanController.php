<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinjamanController extends Controller
{
    public function index(Request $request)
    {
        $loans = DB::table('pinjamen')->where('id_anggota', $request->user()->id)
            ->orderByDesc('created_at')->get();

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
        $data['id_anggota'] = $request->user()->id;
        $data['sisa_pinjaman'] = $data['nominal'];
        $data['angsuran'] = $data['nominal'] / $data['tenor'];
        $data['status'] = 'ditolak';
        $data['keterangan'] = ($data['keterangan'] ?? null);
        $id = DB::table('pinjamen')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);

        return response()->json(['message' => 'Pengajuan pinjaman berhasil dikirim.', 'id' => $id], 201);
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
        $loan = DB::table('pinjamen')->where('id_anggota', $request->user()->id)
            ->where('status', 'aktif')->first();
        return response()->json(['active' => (bool) $loan, 'pinjaman' => $loan]);
    }
}
