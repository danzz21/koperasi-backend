<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Aksi admin terhadap akun anggota (dipakai panel Filament):
 * reset password, aktif/nonaktif, dan hapus.
 *
 * Semua perubahan melewati model sehingga otomatis tercatat di audit trail.
 */
class MemberAccountActions
{
    /** Password default saat admin reset ulang akun anggota. */
    public const DEFAULT_PASSWORD = '123';

    /**
     * Reset password anggota ke default dan paksa login ulang
     * (semua token perangkat lama dicabut).
     */
    public static function resetPassword(User $user, string $password = self::DEFAULT_PASSWORD): User
    {
        // Cast 'hashed' pada model akan meng-hash otomatis
        $user->password = $password;
        $user->save();

        $user->tokens()->delete();

        return $user;
    }

    /** Aktifkan / nonaktifkan akun anggota. */
    public static function setActive(User $user, bool $active): User
    {
        $user->status = $active ? 'aktif' : 'nonaktif';
        $user->save();

        // Anggota yang dinonaktifkan langsung kehilangan akses
        if (! $active) {
            $user->tokens()->delete();
        }

        return $user;
    }

    /** Hapus akun anggota beserta relasinya (cascade di database). */
    public static function delete(User $user): bool
    {
        $user->tokens()->delete();

        return (bool) $user->delete();
    }

    /**
     * Ringkasan data anggota untuk halaman detail admin.
     *
     * @return array<string, mixed>
     */
    public static function summary(User $user): array
    {
        $simpanan = DB::table('simpanans')
            ->where('id_anggota', $user->id)
            ->where('status', 'aktif')
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis='pokok' THEN nominal ELSE 0 END),0) AS pokok")
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis='wajib' THEN nominal ELSE 0 END),0) AS wajib")
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis='sukarela' THEN nominal ELSE 0 END),0) AS sukarela")
            ->first();

        $pinjamanAktif = DB::table('pinjamen')
            ->where('id_anggota', $user->id)
            ->where('status', 'aktif')
            ->count();

        return [
            'simpanan_pokok' => (float) ($simpanan->pokok ?? 0),
            'simpanan_wajib' => (float) ($simpanan->wajib ?? 0),
            'simpanan_sukarela' => (float) ($simpanan->sukarela ?? 0),
            'total_simpanan' => (float) (($simpanan->pokok ?? 0) + ($simpanan->wajib ?? 0) + ($simpanan->sukarela ?? 0)),
            'saldo_ewallet' => (float) DB::table('ewallets')
                ->where('id_anggota', $user->id)->sum('saldo'),
            'pinjaman_aktif' => $pinjamanAktif,
            'jumlah_passkey' => $user->passkeys()->count(),
            'jumlah_topup_pending' => (int) DB::table('ewallet_transactions')
                ->where('id_anggota', $user->id)->where('tipe', 'topup')->where('status', 'pending')->count(),
        ];
    }
}
