<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $savings = DB::table('simpanans')->where('status', 'aktif');
        $loans = DB::table('pinjamen')->where('status', 'aktif');

        return [
            'anggota_aktif' => DB::table('users')->where('role', 'anggota')->where('status', 'aktif')->count(),
            'total_simpanan' => (float) $savings->sum('nominal'),
            'total_pinjaman' => (float) $loans->sum('nominal'),
            'sisa_pinjaman' => (float) $loans->sum('sisa_pinjaman'),
            'pinjaman_aktif' => $loans->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'anggota' => DB::table('users')->where('role', 'anggota')->count(),
            'pending_anggota' => DB::table('users')->where('role', 'anggota')->where('status', 'pending')->count(),
            'pending_pinjaman' => DB::table('pinjamen')->where('status', 'pending')->count(),
            'pending_pembayaran' => DB::table('pembayarans')->where('status', 'pending')->count(),
            'pending_ppob' => DB::table('ppob_transaksi')->where('status', 'pending')->count(),
        ];
    }
}
