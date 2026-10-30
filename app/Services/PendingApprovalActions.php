<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use App\Support\MemberNotifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Logika persetujuan/penolakan tiap kategori "Persetujuan Pending".
 *
 * Dipisah dari halaman Filament agar bisa dipakai ulang (API, command, dsb).
 * Perubahan tetap terekam otomatis lewat App\Observers\AuditObserver.
 */
class PendingApprovalActions
{
    public const ANGGOTA = 'anggota';

    public const SUKARELA = 'sukarela';

    public const POKOK = 'pokok';

    public const WAJIB = 'wajib';

    public const PINJAMAN = 'pinjaman';

    public const PEMBAYARAN = 'pembayaran';

    public static function approve(string $type, Model $record): bool
    {
        return match ($type) {
            self::ANGGOTA => self::approveAnggota($record),
            self::PINJAMAN => self::approvePinjaman($record),
            self::PEMBAYARAN => self::approvePembayaran($record),
            self::SUKARELA,
            self::POKOK,
            self::WAJIB => self::approveSimpanan($record),
            default => false,
        };
    }

    public static function reject(string $type, Model $record): bool
    {
        return match ($type) {
            self::ANGGOTA => self::rejectAnggota($record),
            self::PINJAMAN => self::rejectPinjaman($record),
            self::PEMBAYARAN => self::rejectPembayaran($record),
            self::SUKARELA,
            self::POKOK,
            self::WAJIB => self::rejectSimpanan($record),
            default => false,
        };
    }

    /* ───────────────────────── Anggota baru ───────────────────────── */

    private static function approveAnggota(Model $record): bool
    {
        /** @var User $record */
        $record->status = 'aktif';

        // Data pendaftaran sudah diverifikasi admin → tidak perlu onboarding lagi
        $record->profile_completed_at ??= now();

        $saved = (bool) $record->save();

        if ($saved) {
            MemberNotifier::send($record, 'Pendaftaran disetujui', 'Akun koperasi Anda telah aktif.', 'anggota.approved', '/anggota/dashboard');
        }

        return $saved;
    }

    private static function rejectAnggota(Model $record): bool
    {
        // 'nonaktif' = pendaftaran ditolak, sesuai enum users.status
        if ($record->status !== 'pending') {
            return false;
        }

        $saved = (bool) $record->update(['status' => 'nonaktif']);

        if ($saved && $record instanceof User) {
            MemberNotifier::send($record, 'Pendaftaran ditolak', 'Pendaftaran anggota Anda belum dapat disetujui. Hubungi pengurus untuk informasi lebih lanjut.', 'anggota.rejected');
        }

        return $saved;
    }

    /* ───────────────────── Simpanan (sukarela/pokok) ───────────────────── */

    private static function approveSimpanan(Model $record): bool
    {
        /** @var Simpanan $record */
        if ($record->status !== 'tidak_aktif') {
            return false;
        }

        if (DB::table('withdrawal_requests')
            ->where('id_anggota', $record->id_anggota)
            ->whereIn('status', ['pending', 'disetujui'])->exists()) {
            return false;
        }

        $saved = (bool) $record->update(['status' => 'aktif']);

        if ($saved && $record instanceof Simpanan) {
            MemberNotifier::send($record->anggota, 'Setoran simpanan diverifikasi', 'Setoran simpanan Anda telah diverifikasi dan ditambahkan ke saldo.', 'simpanan.approved', '/anggota/simpanan');
        }

        return $saved;
    }

    private static function rejectSimpanan(Model $record): bool
    {
        /** @var Simpanan $record */
        if ($record->status !== 'tidak_aktif') {
            return false;
        }

        $saved = (bool) $record->update(['status' => 'ditolak']);

        if ($saved && $record instanceof Simpanan) {
            MemberNotifier::send($record->anggota, 'Setoran simpanan ditolak', 'Setoran simpanan Anda ditolak. Hubungi pengurus untuk informasi lebih lanjut.', 'simpanan.rejected', '/anggota/simpanan');
        }

        return $saved;
    }

    /* ───────────────────────── Pinjaman ───────────────────────── */

    private static function approvePinjaman(Model $record): bool
    {
        /** @var Pinjaman $record */
        return DB::transaction(function () use ($record): bool {
            $loan = Pinjaman::query()->lockForUpdate()->findOrFail($record->getKey());

            if ($loan->status !== 'pending') {
                return false;
            }

            $loan->status = 'disetujui';
            $loan->tgl_persetujuan = $loan->tgl_persetujuan ?: now()->toDateString();
            $loan->sisa_pinjaman = $loan->nominal;
            $loan->angsuran = (float) $loan->nominal / (int) $loan->tenor;
            $loan->cicilan_ke = 0;
            $loan->save();

            $totalCents = (int) round((float) $loan->nominal * 100);
            $baseCents = intdiv($totalCents, (int) $loan->tenor);
            $remainder = $totalCents % (int) $loan->tenor;
            $startDate = now();

            for ($i = 1; $i <= (int) $loan->tenor; $i++) {
                $cents = $baseCents + ($i <= $remainder ? 1 : 0);
                DB::table('cicilans')->insert([
                    'id_pinjaman' => $loan->id,
                    'id_anggota' => $loan->id_anggota,
                    'cicilan_ke' => $i,
                    'nominal' => $cents / 100,
                    'tgl_tempo' => $startDate->copy()->addMonthsNoOverflow($i)->toDateString(),
                    'status' => 'belumbayar',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            MemberNotifier::send(
                $loan->anggota,
                'Pinjaman disetujui',
                'Pengajuan pinjaman disetujui. Pengurus sedang memproses pencairan dana.',
                'pinjaman.approved',
                '/anggota/pinjaman',
            );

            return true;
        });
    }

    private static function rejectPinjaman(Model $record): bool
    {
        /** @var Pinjaman $record */
        if ($record->status !== 'pending') {
            return false;
        }

        $saved = (bool) $record->update(['status' => 'ditolak']);

        if ($saved && $record instanceof Pinjaman) {
            MemberNotifier::send($record->anggota, 'Pengajuan pinjaman ditolak', 'Pengajuan pinjaman Anda belum dapat disetujui. Hubungi pengurus untuk informasi lebih lanjut.', 'pinjaman.rejected', '/anggota/pinjaman');
        }

        return $saved;
    }

    public static function disbursePinjaman(Pinjaman $loan): bool
    {
        return DB::transaction(function () use ($loan): bool {
            $lockedLoan = Pinjaman::query()->lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== 'disetujui') {
                return false;
            }

            $lockedLoan->update([
                'status' => 'aktif',
                'tgl_cair' => now()->toDateString(),
            ]);

            MemberNotifier::send(
                $lockedLoan->anggota,
                'Dana pinjaman dicairkan',
                'Dana pinjaman Anda telah dicatat sebagai dicairkan. Jadwal cicilan sudah tersedia di aplikasi.',
                'pinjaman.disbursed',
                '/anggota/cicilan',
            );

            return true;
        });
    }

    /* ───────────────────────── Pembayaran ───────────────────────── */

    private static function approvePembayaran(Model $record): bool
    {
        /** @var Pembayaran $record */
        $saved = DB::transaction(function () use ($record): bool {
            $payment = Pembayaran::query()->lockForUpdate()->findOrFail($record->getKey());
            if ($payment->status !== 'pending') {
                return false;
            }

            $payment->update([
                'status' => 'diverifikasi',
                'tgl_pembayaran' => now(),
            ]);

            if ($payment->id_cicilan) {
                InstallmentSettlement::settle((int) $payment->id_cicilan);
            }

            return true;
        });

        if ($saved && $record instanceof Pembayaran) {
            MemberNotifier::send($record->anggota, 'Pembayaran cicilan terverifikasi', 'Pembayaran cicilan Anda telah diverifikasi.', 'cicilan.payment_approved', '/anggota/cicilan');
        }

        return $saved;
    }

    private static function rejectPembayaran(Model $record): bool
    {
        /** @var Pembayaran $record */
        if ($record->status !== 'pending') {
            return false;
        }

        $saved = (bool) $record->update(['status' => 'gagal']);

        if ($saved && $record instanceof Pembayaran) {
            MemberNotifier::send($record->anggota, 'Pembayaran cicilan ditolak', 'Pembayaran cicilan Anda ditolak. Silakan hubungi pengurus.', 'cicilan.payment_rejected', '/anggota/cicilan');
        }

        return $saved;
    }
}
