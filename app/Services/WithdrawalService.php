<?php

namespace App\Services;

use App\Models\SystemConfig;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function history(User $user)
    {
        return WithdrawalRequest::query()->where('id_anggota', $user->id)->latest()->get();
    }

    public function submit(User $user): WithdrawalRequest
    {
        return DB::transaction(function () use ($user): WithdrawalRequest {
            $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya anggota aktif yang dapat mengajukan pengunduran diri.',
                ]);
            }

            if (WithdrawalRequest::query()->where('id_anggota', $lockedUser->id)
                ->whereIn('status', ['pending', 'disetujui'])->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Masih ada permohonan pengunduran diri yang sedang diproses.',
                ]);
            }

            $hasOutstandingLoan = DB::table('pinjamen')
                ->where('id_anggota', $lockedUser->id)
                ->whereIn('status', ['pending', 'disetujui', 'aktif'])
                ->exists();

            if ($hasOutstandingLoan) {
                throw ValidationException::withMessages([
                    'pinjaman' => 'Lunasi atau selesaikan seluruh pengajuan pinjaman sebelum mengundurkan diri.',
                ]);
            }

            $hasPendingSavings = DB::table('simpanans')
                ->where('id_anggota', $lockedUser->id)
                ->where('status', 'tidak_aktif')
                ->exists();
            $hasPendingPayment = DB::table('payment_gateway_transaksi')
                ->where('id_anggota', $lockedUser->id)
                ->where('jenis_pembayaran', 'simpanan')
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingSavings || $hasPendingPayment) {
                throw ValidationException::withMessages([
                    'simpanan' => 'Tunggu hingga seluruh setoran simpanan yang sedang diproses selesai.',
                ]);
            }

            if (! $lockedUser->no_rek || ! $lockedUser->jenis_bank || ! $lockedUser->atasnama_rekening) {
                throw ValidationException::withMessages([
                    'rekening' => 'Lengkapi bank, nomor rekening, dan nama pemilik rekening pada profil terlebih dahulu.',
                ]);
            }

            $totalSavings = (float) DB::table('simpanans')
                ->where('id_anggota', $lockedUser->id)
                ->where('status', 'aktif')
                ->sum('nominal');
            $adminFee = (float) SystemConfig::getValue('exit_admin_fee', 0);

            if ($adminFee < 0 || $adminFee > $totalSavings) {
                throw ValidationException::withMessages([
                    'biaya_admin' => 'Biaya administrasi pengunduran diri tidak valid.',
                ]);
            }

            if ($totalSavings <= 0) {
                throw ValidationException::withMessages([
                    'simpanan' => 'Tidak ada simpanan aktif yang dapat dicairkan.',
                ]);
            }

            return WithdrawalRequest::query()->create([
                'id_anggota' => $lockedUser->id,
                'total_simpanan' => $totalSavings,
                'total_kewajiban' => 0,
                'biaya_admin' => $adminFee,
                'jumlah_pencairan' => $totalSavings - $adminFee,
                'nomor_rekening' => $lockedUser->no_rek,
                'jenis_bank' => $lockedUser->jenis_bank,
                'atasnama_rekening' => $lockedUser->atasnama_rekening,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(User $user): array
    {
        $savings = (float) DB::table('simpanans')->where('id_anggota', $user->id)
            ->where('status', 'aktif')->sum('nominal');
        $hasOutstandingLoan = DB::table('pinjamen')->where('id_anggota', $user->id)
            ->whereIn('status', ['pending', 'disetujui', 'aktif'])->exists();
        $fee = (float) SystemConfig::getValue('exit_admin_fee', 0);

        return [
            'total_simpanan' => $savings,
            'biaya_admin' => $fee,
            'jumlah_pencairan' => max($savings - $fee, 0),
            'ada_pinjaman_aktif' => $hasOutstandingLoan,
            'rekening' => [
                'jenis_bank' => $user->jenis_bank,
                'nomor_rekening' => $user->no_rek,
                'atasnama_rekening' => $user->atasnama_rekening,
            ],
        ];
    }
}
