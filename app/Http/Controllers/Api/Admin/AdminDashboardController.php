<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminDashboardController extends Controller {
    public function index() {
        $simpanan = DB::table('simpanans')->where('status', 'aktif');
        $pinjaman = DB::table('pinjamen')->where('status', 'aktif');

        return response()->json([
            'data' => [
                'anggota_aktif' => DB::table('users')->where('role', 'anggota')->where('status', 'aktif')->count(),
                'total_simpanan' => (float) $simpanan->sum('nominal'),
                'total_pinjaman' => (float) $pinjaman->sum('nominal'),
                'sisa_pinjaman' => (float) $pinjaman->sum('sisa_pinjaman'),
                'pinjaman_aktif' => $pinjaman->count(),
            ],
        ]);
    }

    public function stats() {
        return response()->json([
            'anggota' => DB::table('users')->where('role', 'anggota')->count(),
            'pending_anggota' => DB::table('users')->where('role', 'anggota')->where('status', 'pending')->count(),
            'pending_pinjaman' => DB::table('pinjamen')->where('status', 'ditolak')->count(),
            'pending_pembayaran' => DB::table('pembayarans')->where('status', 'pending')->count(),
            'pending_ppob' => DB::table('ppob_transaksi')->where('status', 'pending')->count(),
        ]);
    }
}
