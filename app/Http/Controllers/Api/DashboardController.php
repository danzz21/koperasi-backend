<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $id = (int) $request->user()->id;

        $savings = DB::table('simpanans')->where('id_anggota', $id)->where('status', 'aktif');
        $loans = DB::table('pinjamen')->where('id_anggota', $id)->where('status', 'aktif');

        $data = [
            'total_saldo' => (float) $savings->sum('nominal'),
            'total_pinjaman' => (float) $loans->sum('nominal'),
            'sisa_kewajiban' => (float) $loans->sum('sisa_pinjaman'),
            'sim_pokok' => (float) (clone $savings)->where('jenis', 'pokok')->sum('nominal'),
            'sim_wajib' => (float) (clone $savings)->where('jenis', 'wajib')->sum('nominal'),
            'sim_sukarela' => (float) (clone $savings)->where('jenis', 'sukarela')->sum('nominal'),
            'pinjaman_aktif' => $loans->count(),
        ];

        return response()->json([
            'data' => $data,
            'user' => $request->user(),
            'summary' => $data,
            'recent_payments' => DB::table('pembayarans')->where('id_anggota', $id)
                ->orderByDesc('created_at')->limit(5)->get(),
        ]);
    }
}
