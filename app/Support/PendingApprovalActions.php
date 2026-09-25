<?php

namespace App\Support;

use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Logika persetujuan/penolakan tiap kategori "Persetujuan Pending".
 *
 * Dipisah dari halaman Filament agar bisa dipakai ulang (API, command, dsb).
 * Perubahan tetap terekam otomatis lewat App\Observers\AuditObserver.
 */
class PendingApprovalActions
{
    public const ANGGOTA    = 'anggota';
    public const SUKARELA   = 'sukarela';
    public const POKOK      = 'pokok';
    public const PINJAMAN   = 'pinjaman';
    public const PEMBAYARAN = 'pembayaran';

    public static function approve(string $type, Model $record): bool
    {
        return match ($type) {
            self::ANGGOTA    => self::approveAnggota($record),
            self::PINJAMAN   => self::approvePinjaman($record),
            self::PEMBAYARAN => self::approvePembayaran($record),
            self::SUKARELA,
            self::POKOK      => self::approveSimpanan($record),
            default          => false,
        };
    }

    public static function reject(string $type, Model $record): bool
    {
        return match ($type) {
            self::ANGGOTA    => self::rejectAnggota($record),
            self::PINJAMAN   => self::rejectPinjaman($record),
            self::PEMBAYARAN => self::rejectPembayaran($record),
            self::SUKARELA,
            self::POKOK      => self::rejectSimpanan($record),
            default          => false,
        };
    }

    /* ───────────────────────── Anggota baru ───────────────────────── */

    private static function approveAnggota(Model $record): bool
    {
        /** @var User $record */
        $record->status = 'aktif';

        // Data pendaftaran sudah diverifikasi admin → tidak perlu onboarding lagi
        $record->profile_completed_at ??= now();

        return (bool) $record->save();
    }

    private static function rejectAnggota(Model $record): bool
    {
        // 'nonaktif' = pendaftaran ditolak, sesuai enum users.status
        return (bool) $record->update(['status' => 'nonaktif']);
    }

    /* ───────────────────── Simpanan (sukarela/pokok) ───────────────────── */

    private static function approveSimpanan(Model $record): bool
    {
        /** @var Simpanan $record */
        return (bool) $record->update(['status' => 'aktif']);
    }

    private static function rejectSimpanan(Model $record): bool
    {
        /** @var Simpanan $record */
        return (bool) $record->update(['status' => 'ditolak']);
    }

    /* ───────────────────────── Pinjaman ───────────────────────── */

    private static function approvePinjaman(Model $record): bool
    {
        /** @var Pinjaman $record */
        $record->status          = 'aktif';
        $record->tgl_persetujuan = $record->tgl_persetujuan ?: now()->toDateString();
        $record->tgl_cair        = $record->tgl_cair ?: now()->toDateString();
        $record->sisa_pinjaman   = ((float) $record->sisa_pinjaman) > 0
            ? $record->sisa_pinjaman
            : $record->nominal;

        if (((float) $record->angsuran) <= 0 && ((int) $record->tenor) > 0) {
            $record->angsuran = $record->nominal / $record->tenor;
        }

        $record->cicilan_ke = $record->cicilan_ke ?? 0;

        return (bool) $record->save();
    }

    private static function rejectPinjaman(Model $record): bool
    {
        /** @var Pinjaman $record */
        return (bool) $record->update(['status' => 'ditolak']);
    }

    /* ───────────────────────── Pembayaran ───────────────────────── */

    private static function approvePembayaran(Model $record): bool
    {
        /** @var Pembayaran $record */
        return (bool) $record->update([
            'status'         => 'diverifikasi',
            'tgl_pembayaran' => now(),
        ]);
    }

    private static function rejectPembayaran(Model $record): bool
    {
        /** @var Pembayaran $record */
        return (bool) $record->update(['status' => 'gagal']);
    }
}
