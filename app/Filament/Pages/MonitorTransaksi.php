<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class MonitorTransaksi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Semua Transaksi';

    protected static ?string $title = 'Monitor Semua Transaksi';

    protected static string|UnitEnum|null $navigationGroup = 'Keamanan & Audit';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.monitor-transaksi';

    public function getSubheading(): ?string
    {
        return 'Audit menyeluruh seluruh arus transaksi koperasi tanpa pengecualian';
    }

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'superadmin'
            && auth()->user()?->role === 'superadmin';
    }

    public function getViewData(): array
    {
        // Simpanan terbaru
        $simpanan = DB::table('simpanans')
            ->join('users', 'users.id', '=', 'simpanans.id_anggota')
            ->select('simpanans.*', 'users.nama_lengkap')
            ->latest('simpanans.created_at')
            ->limit(15)
            ->get();

        // Pinjaman terbaru
        $pinjaman = DB::table('pinjamen')
            ->join('users', 'users.id', '=', 'pinjamen.id_anggota')
            ->select('pinjamen.*', 'users.nama_lengkap')
            ->latest('pinjamen.created_at')
            ->limit(15)
            ->get();

        // PPOB terbaru
        $ppob = DB::table('ppob_transaksi')
            ->join('users', 'users.id', '=', 'ppob_transaksi.id_anggota')
            ->select('ppob_transaksi.*', 'users.nama_lengkap')
            ->latest('ppob_transaksi.created_at')
            ->limit(15)
            ->get();

        // Pembayaran terbaru
        $pembayaran = DB::table('pembayarans')
            ->leftJoin('users', 'users.id', '=', 'pembayarans.id_anggota')
            ->select('pembayarans.*', 'users.nama_lengkap')
            ->latest('pembayarans.created_at')
            ->limit(15)
            ->get();

        return [
            'simpanan'   => $simpanan,
            'pinjaman'   => $pinjaman,
            'ppob'       => $ppob,
            'pembayaran' => $pembayaran,
        ];
    }
}
