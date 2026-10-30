<?php

namespace App\Support;

use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Satu sumber kebenaran untuk daftar & hitungan "Persetujuan Pending".
 *
 * Dipakai kartu di dashboard admin DAN halaman detail masing-masing kategori,
 * supaya angka pada kartu selalu sama dengan jumlah baris di halamannya.
 */
class PendingApprovals
{
    /** Pendaftaran anggota baru yang menunggu verifikasi admin. */
    public static function anggota(): Builder
    {
        return User::query()
            ->where('role', 'anggota')
            ->where('status', 'pending');
    }

    /** Setoran simpanan sukarela yang menunggu konfirmasi. */
    public static function sukarela(): Builder
    {
        return Simpanan::query()
            ->where('jenis', 'sukarela')
            ->where('status', 'tidak_aktif');
    }

    /** Setoran simpanan pokok yang menunggu verifikasi. */
    public static function pokok(): Builder
    {
        return Simpanan::query()
            ->where('jenis', 'pokok')
            ->where('status', 'tidak_aktif');
    }

    public static function wajib(): Builder
    {
        return Simpanan::query()
            ->where('jenis', 'wajib')
            ->where('status', 'tidak_aktif');
    }

    /** Pengajuan pinjaman (akad) yang menunggu persetujuan. */
    public static function pinjaman(): Builder
    {
        return Pinjaman::query()->where('status', 'pending');
    }

    /** Pembayaran cicilan yang menunggu verifikasi. */
    public static function pembayaran(): Builder
    {
        return Pembayaran::query()
            ->where('jenis', 'cicilan')
            ->where('status', 'pending');
    }

    /**
     * Jumlah tiap kategori pending + totalnya.
     *
     * @return array{anggota: int, sukarela: int, pokok: int, wajib: int, pinjaman: int, pembayaran: int, total: int}
     */
    public static function counts(): array
    {
        $counts = [
            'anggota' => static::anggota()->count(),
            'sukarela' => static::sukarela()->count(),
            'pokok' => static::pokok()->count(),
            'wajib' => static::wajib()->count(),
            'pinjaman' => static::pinjaman()->count(),
            'pembayaran' => static::pembayaran()->count(),
        ];

        $counts['total'] = array_sum($counts);

        return $counts;
    }
}
