<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicilanController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id;
        $loans = DB::table('pinjamen')->where('id_anggota', $id)->get();
        $payments = DB::table('pembayarans')->where('id_anggota', $id)->orderByDesc('created_at')->get();
        $schedule = DB::table('cicilans')->where('id_anggota', $id)->orderBy('cicilan_ke')->get();
        return response()->json(['anggota' => $request->user(),
            'pinjaman_aktif' => $loans->where('status', 'aktif')->values(),
            'pinjaman_lunas' => $loans->where('status', 'lunas')->values(),
            'pembayaran_pending' => $payments->where('status', 'pending')->values(),
            'data' => $schedule, 'riwayat_pembayaran' => $payments, 'summary' => [
                'total_pinjaman_aktif' => $loans->where('status', 'aktif')->count(),
                'total_sisa_kewajiban' => (float) $loans->where('status', 'aktif')->sum('sisa_pinjaman'),
            ]]);
    }

    public function bayar(Request $request)
    {
        $data = $request->validate([
            'id_pinjaman' => 'required|integer', 'nominal' => 'required|numeric|min:1',
            'metode' => 'nullable|in:transfer,tunai,e-wallet',
        ]);
        $loan = DB::table('pinjamen')->where('id', $data['id_pinjaman'])
            ->where('id_anggota', $request->user()->id)->first();
        if (!$loan) return response()->json(['message' => 'Pinjaman tidak ditemukan.'], 404);
        DB::table('pembayarans')->insert([
            'id_anggota' => $request->user()->id, 'no_referensi' => 'CIC-' . now()->format('ymdHis') . random_int(100, 999),
            'jenis' => 'cicilan', 'nominal' => $data['nominal'], 'metode' => $data['metode'] ?? 'transfer',
            'status' => 'pending', 'keterangan' => 'Pembayaran cicilan', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Pembayaran berhasil dikirim.'], 201);
    }

    public function riwayat(Request $request)
    {
        return response()->json(['riwayat' => DB::table('pembayarans')
            ->where('id_anggota', $request->user()->id)->where('jenis', 'cicilan')
            ->orderByDesc('created_at')->get()]);
    }

    public function setTenor(Request $request)
    {
        $request->validate(['tenor' => 'required|integer|min:1|max:60']);
        return response()->json(['message' => 'Tenor berhasil disimpan.', 'tenor' => (int) $request->tenor]);
    }
}
