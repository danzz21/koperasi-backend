<?php

namespace App\Services;

use App\Models\Simpanan;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Support\MemberNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalRequestActions
{
    public static function approve(WithdrawalRequest $request): bool
    {
        return DB::transaction(function () use ($request): bool {
            $locked = WithdrawalRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== 'pending') {
                return false;
            }

            $locked->update(['status' => 'disetujui', 'disetujui_pada' => now()]);
            MemberNotifier::send($locked->anggota, 'Pengunduran diri disetujui', 'Permohonan Anda disetujui. Pengurus akan memproses transfer sisa simpanan.', 'withdrawal.approved');

            return true;
        });
    }

    public static function reject(WithdrawalRequest $request, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($request, $reason): bool {
            $locked = WithdrawalRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== 'pending') {
                return false;
            }

            $locked->update(['status' => 'ditolak', 'catatan' => $reason]);
            MemberNotifier::send(
                $locked->anggota,
                'Pengunduran diri ditolak',
                $reason ?: 'Permohonan Anda ditolak. Hubungi pengurus untuk informasi lebih lanjut.',
                'withdrawal.rejected',
            );

            return true;
        });
    }

    public static function markPaid(WithdrawalRequest $request): bool
    {
        return DB::transaction(function () use ($request): bool {
            $locked = WithdrawalRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== 'disetujui') {
                return false;
            }

            $savings = Simpanan::query()
                ->where('id_anggota', $locked->id_anggota)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->get();
            $currentTotal = (float) $savings->sum('nominal');

            if (round($currentTotal, 2) !== round((float) $locked->total_simpanan, 2)) {
                throw ValidationException::withMessages([
                    'simpanan' => 'Saldo simpanan berubah setelah permohonan dibuat. Periksa kembali jumlah pencairan sebelum memproses transfer.',
                ]);
            }

            $savings->each(fn (Simpanan $saving) => $saving->update(['status' => 'ditarik']));
            $locked->update(['status' => 'dicairkan', 'dicairkan_pada' => now()]);
            $member = User::query()->findOrFail($locked->id_anggota);
            $member->update(['status' => 'nonaktif']);

            MemberNotifier::send(
                $member,
                'Pengunduran diri selesai',
                'Transfer sisa simpanan telah dikonfirmasi dan keanggotaan Anda ditutup.',
                'withdrawal.paid',
            );
            $member->tokens()->delete();

            return true;
        });
    }
}
