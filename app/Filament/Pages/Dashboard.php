<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard Utama';
    protected static ?int $navigationSort = -2;

    public function getView(): string
    {
        return 'filament.pages.dashboard';
    }

    public function getViewData(): array
    {
        // --- Simpanan ---
        $simpananRows = DB::table('simpanans')->where('status', 'aktif')
            ->selectRaw("COALESCE(SUM(nominal),0) AS total")
            ->first();

        $totalSimpanan   = (float) ($simpananRows->total ?? 0);
        $kasReal         = $totalSimpanan * 0.8;

        // --- Pinjaman ---
        $pinjamanRow = DB::table('pinjamen')->where('status', 'aktif')
            ->selectRaw('COALESCE(SUM(nominal),0) AS nominal')
            ->selectRaw('COALESCE(SUM(sisa_pinjaman),0) AS sisa')
            ->selectRaw('COALESCE(SUM(angsuran),0) AS angsuran_total')
            ->first();

        $sisaPokok       = (float) ($pinjamanRow->sisa ?? 0);
        $realisasiMargin = (float) ($pinjamanRow->nominal ?? 0) * 0.10;
        $potensiMargin   = $sisaPokok * 0.10;
        $shuBerjalan     = (float) ($pinjamanRow->angsuran_total ?? 0) * 0.12;
        $totalAset       = $kasReal + $sisaPokok;

        // --- Pembayaran ---
        $pemasukanOpr = (float) DB::table('pembayarans')
            ->where('status', 'selesai')
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        // --- Cicilan ---
        $tagihanBulanIni = (float) DB::table('cicilans')
            ->where('status', 'belumbayar')
            ->whereMonth('tgl_tempo', now()->month)
            ->sum('nominal');

        // --- Anggota ---
        $totalAnggota = DB::table('users')->where('role', 'anggota')->where('status', 'aktif')->count();

        // --- Rasio ---
        $rasio = $totalSimpanan > 0 ? round(($sisaPokok / $totalSimpanan) * 100, 1) : 0;

        // --- Pending ---
        $pendingAnggota  = DB::table('users')->where('role', 'anggota')->where('status', 'pending')->count();
        $pendingSukarela = DB::table('simpanans')->where('status', 'tidak_aktif')->where('jenis', 'sukarela')->count();
        $pendingPokok    = DB::table('simpanans')->where('status', 'tidak_aktif')->where('jenis', 'pokok')->count();
        $pendingPinjaman = DB::table('pinjamen')->where('status', 'pending')->count();
        $pendingBayar    = DB::table('pembayarans')->where('status', 'pending')->count();

        // --- Chart ---
        $chartLabels     = [];
        $chartSimpanan   = [];
        $chartPembiayaan = [];
        for ($i = 11; $i >= 0; $i--) {
            $date            = now()->startOfMonth()->subMonths($i);
            $chartLabels[]   = $date->translatedFormat('M');
            $chartSimpanan[] = (float) DB::table('simpanans')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('nominal');
            $chartPembiayaan[] = (float) DB::table('pinjamen')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('nominal');
        }

        return [
            'kasReal'          => $kasReal,
            'shuBerjalan'      => $shuBerjalan,
            'totalAset'        => $totalAset,
            'totalSimpanan'    => $totalSimpanan,
            'sisaPokok'        => $sisaPokok,
            'realisasiMargin'  => $realisasiMargin,
            'pemasukanOpr'     => $pemasukanOpr,
            'bebanOpr'         => 200000,
            'tagihanBulanIni'  => $tagihanBulanIni,
            'potensiMargin'    => $potensiMargin,
            'totalAnggota'     => $totalAnggota,
            'rasio'            => $rasio,
            'filterLabel'      => 'Tahun Ini (' . now()->year . ')',
            'pendingAnggota'   => $pendingAnggota,
            'pendingSukarela'  => $pendingSukarela,
            'pendingPokok'     => $pendingPokok,
            'pendingPinjaman'  => $pendingPinjaman,
            'pendingBayar'     => $pendingBayar,
            'totalPending'     => $pendingAnggota + $pendingSukarela + $pendingPokok + $pendingPinjaman + $pendingBayar,
            'chartLabels'      => json_encode($chartLabels),
            'chartSimpanan'    => json_encode($chartSimpanan),
            'chartPembiayaan'  => json_encode($chartPembiayaan),
        ];
    }
}
