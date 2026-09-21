<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class LaporanKeuangan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?string $title = 'Laporan Keuangan';

    protected static string|UnitEnum|null $navigationGroup = 'Keamanan & Audit';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.laporan-keuangan';

    public function getSubheading(): ?string
    {
        return 'Laba rugi & neraca ringkas koperasi — periode ' . now()->year;
    }

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'superadmin'
            && auth()->user()?->role === 'superadmin';
    }

    public function getViewData(): array
    {
        $year = now()->year;

        // ── Pendapatan ─────────────────────────────────────────────────────
        $marginMurabahah = (float) DB::table('pinjamen')
            ->whereIn('status', ['aktif', 'lunas'])
            ->sum(DB::raw('angsuran * 0.10'));

        $shuMudharabah = (float) DB::table('pinjamen')
            ->where('tipe', 'mudharabah')
            ->whereIn('status', ['aktif', 'lunas'])
            ->sum(DB::raw('angsuran * 0.60'));

        $pendapatanPpob = (float) DB::table('ppob_transaksi')
            ->where('status', 'success')
            ->whereYear('created_at', $year)
            ->sum(DB::raw('harga - nominal'));

        $totalPendapatan = $marginMurabahah + $shuMudharabah + $pendapatanPpob;

        // ── Beban ──────────────────────────────────────────────────────────
        $bebanOperasional = 2400000;
        $totalBeban       = $bebanOperasional;
        $labaBersih       = $totalPendapatan - $totalBeban;

        // ── Neraca ─────────────────────────────────────────────────────────
        $totalSimpanan = (float) DB::table('simpanans')->where('status', 'aktif')->sum('nominal');
        $piutang       = (float) DB::table('pinjamen')->where('status', 'aktif')->sum('sisa_pinjaman');
        $kas           = max($totalSimpanan - $piutang, 0) + $labaBersih;
        $totalAset     = $kas + $piutang;
        $kewajiban     = $totalSimpanan;
        $ekuitas       = $totalAset - $kewajiban;

        // ── Simpanan per jenis ─────────────────────────────────────────────
        $simpananPerJenis = DB::table('simpanans')
            ->where('status', 'aktif')
            ->select('jenis', DB::raw('SUM(nominal) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis')
            ->get();

        // ── Arus kas bulanan ───────────────────────────────────────────────
        $arusKas = [];
        for ($month = 1; $month <= 12; $month++) {
            $masuk = (float) DB::table('transaksi')
                ->where('tipe', 'kredit')
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            $keluar = (float) DB::table('transaksi')
                ->where('tipe', 'debet')
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            $arusKas[] = [
                'label'  => now()->setMonth($month)->translatedFormat('M'),
                'masuk'  => $masuk,
                'keluar' => $keluar,
            ];
        }

        return [
            'year'              => $year,
            'marginMurabahah'   => $marginMurabahah,
            'shuMudharabah'     => $shuMudharabah,
            'pendapatanPpob'    => $pendapatanPpob,
            'totalPendapatan'   => $totalPendapatan,
            'bebanOperasional'  => $bebanOperasional,
            'totalBeban'        => $totalBeban,
            'labaBersih'        => $labaBersih,
            'kas'               => $kas,
            'piutang'           => $piutang,
            'totalAset'         => $totalAset,
            'kewajiban'         => $kewajiban,
            'ekuitas'           => $ekuitas,
            'simpananPerJenis'  => $simpananPerJenis,
            'arusKas'           => $arusKas,
        ];
    }
}
