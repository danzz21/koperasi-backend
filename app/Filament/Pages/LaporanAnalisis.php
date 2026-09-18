<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanAnalisis extends Page
{
    protected static string|BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'Laporan & Analisis';
    protected static ?string $title = 'Laporan & Analisis';
    protected static ?int $navigationSort = 10;
    protected static string|UnitEnum|null $navigationGroup = 'Laporan & Sistem';
    protected string $view = 'filament.pages.laporan-analisis';

    public function getViewData(): array
    {
        $year = now()->year;
        $pemasukan = (float) DB::table('transaksi')->where('tipe', 'kredit')->whereYear('tanggal', $year)->sum('jumlah');
        $pengeluaran = (float) DB::table('transaksi')->where('tipe', 'debet')->whereYear('tanggal', $year)->sum('jumlah');
        $simpanan = (float) DB::table('simpanans')->where('status', 'aktif')->sum('nominal');
        $pembiayaan = (float) DB::table('pinjamen')->where('status', 'aktif')->sum('sisa_pinjaman');

        $monthly = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthly[] = [
                'label' => now()->setMonth($month)->translatedFormat('M'),
                'pemasukan' => (float) DB::table('transaksi')->where('tipe', 'kredit')->whereYear('tanggal', $year)->whereMonth('tanggal', $month)->sum('jumlah'),
                'pengeluaran' => (float) DB::table('transaksi')->where('tipe', 'debet')->whereYear('tanggal', $year)->whereMonth('tanggal', $month)->sum('jumlah'),
            ];
        }

        return compact('year', 'pemasukan', 'pengeluaran', 'simpanan', 'pembiayaan', 'monthly');
    }
}
