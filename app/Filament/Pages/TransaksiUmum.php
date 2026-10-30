<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TransaksiUmum extends Page
{
    protected static ?string $navigationLabel = 'Transaksi Umum';
    protected static ?int    $navigationSort  = 5;
    protected static string  $routePath       = 'transaksi-umum';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-arrows-right-left';
    }

    public function getView(): string
    {
        return 'filament.pages.transaksi-umum';
    }

    public function getTitle(): string { return ''; }

    public function getViewData(): array
    {
        // Filter dari request
        $jenis     = request('jenis', '');
        $bulan     = request('bulan', now()->format('Y-m'));
        $cari      = request('cari', '');

        [$tahun, $bln] = explode('-', $bulan . '-01');

        // Stat: total kas (semua pembayaran selesai)
        $totalKas = (float) DB::table('pembayarans')
            ->where('status', 'selesai')->sum('nominal');

        // Arus kas bulan ini
        $pemasukanBulan = (float) DB::table('pembayarans')
            ->where('status', 'selesai')
            ->whereYear('tgl_pembayaran', $tahun)
            ->whereMonth('tgl_pembayaran', $bln)
            ->sum('nominal');

        $pengeluaranBulan = 0; // Tidak ada tabel pengeluaran, placeholder

        $arusBulan = $pemasukanBulan - $pengeluaranBulan;

        // Query transaksi (dari pembayarans)
        $query = DB::table('pembayarans as p')
            ->join('users as u', 'u.id', '=', 'p.id_anggota')
            ->select(
                'p.id', 'p.jenis', 'p.nominal', 'p.metode', 'p.status',
                'p.keterangan', 'p.no_referensi',
                'p.tgl_pembayaran', 'p.created_at',
                'u.nama_lengkap'
            )
            ->orderByDesc('p.created_at');

        if ($jenis) {
            $query->where('p.jenis', $jenis);
        }
        if ($bulan) {
            $query->whereYear('p.created_at', $tahun)
                  ->whereMonth('p.created_at', $bln);
        }
        if ($cari) {
            $query->where(function($q) use ($cari) {
                $q->where('u.nama_lengkap', 'like', "%$cari%")
                  ->orWhere('p.keterangan', 'like', "%$cari%")
                  ->orWhere('p.no_referensi', 'like', "%$cari%");
            });
        }

        $transaksis = $query->paginate(15);

        return [
            'totalKas'        => $totalKas,
            'pemasukanBulan'  => $pemasukanBulan,
            'pengeluaranBulan'=> $pengeluaranBulan,
            'arusBulan'       => $arusBulan,
            'transaksis'      => $transaksis,
            'jenis'           => $jenis,
            'bulan'           => $bulan,
            'cari'            => $cari,
            'bulanLabel'       => \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y'),
        ];
    }
}
