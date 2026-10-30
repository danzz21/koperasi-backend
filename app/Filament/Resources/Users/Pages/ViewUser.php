<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getView(): string
    {
        return 'filament.pages.user-detail';
    }

    public function getTitle(): string
    {
        return 'Detail Anggota';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('← Kembali ke Daftar Anggota')
                ->color('gray')
                ->url(UserResource::getUrl('index')),

            EditAction::make()
                ->label('Edit Data Anggota')
                ->color('info'),
        ];
    }

    public function getViewData(): array
    {
        /** @var User $user */
        $user = $this->getRecord();

        // Simpanan — kolom: id_anggota, jenis, nominal, status
        $simpananRows = DB::table('simpanans')
            ->where('id_anggota', $user->id)
            ->where('status', 'aktif')
            ->select('jenis', DB::raw('SUM(nominal) as total'))
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        $simpananPokok    = (float) ($simpananRows['pokok']    ?? 0);
        $simpananWajib    = (float) ($simpananRows['wajib']    ?? 0);
        $simpananSukarela = (float) ($simpananRows['sukarela'] ?? 0);
        $totalSimpanan    = $simpananPokok + $simpananWajib + $simpananSukarela;

        // Pinjaman aktif — kolom: id_anggota, tipe, nominal, sisa_pinjaman, tenor, angsuran, status
        $pinjamanAktif = DB::table('pinjamen')
            ->where('id_anggota', $user->id)
            ->where('status', 'aktif')
            ->get();

        $totalPembiayaan = (float) $pinjamanAktif->sum('nominal');
        $sisaAngsuran    = (int)   $pinjamanAktif->sum('cicilan_ke'); // pakai cicilan_ke sbg proxy sisa
        $bagiHasil       = $totalSimpanan * 0.05;

        // Cicilan mendatang — kolom: id_anggota, status, tgl_tempo
        $cicilans = DB::table('cicilans')
            ->where('id_anggota', $user->id)
            ->where('status', 'belumbayar')
            ->orderBy('tgl_tempo')
            ->get();

        // Hitung sisa angsuran dari cicilans belumbayar
        $sisaAngsuran = DB::table('cicilans')
            ->where('id_anggota', $user->id)
            ->where('status', 'belumbayar')
            ->count();

        // Riwayat pembayaran — kolom: id_anggota, nominal, status, jenis, tgl_pembayaran
        $riwayat = DB::table('pembayarans')
            ->where('id_anggota', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return [
            'user'             => $user,
            'totalSimpanan'    => $totalSimpanan,
            'simpananPokok'    => $simpananPokok,
            'simpananWajib'    => $simpananWajib,
            'simpananSukarela' => $simpananSukarela,
            'totalPembiayaan'  => $totalPembiayaan,
            'sisaAngsuran'     => $sisaAngsuran,
            'bagiHasil'        => $bagiHasil,
            'pinjamanAktif'    => $pinjamanAktif,
            'cicilans'         => $cicilans,
            'riwayat'          => $riwayat,
        ];
    }
}
