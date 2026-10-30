<?php

namespace App\Services;

use App\Models\Cicilan;
use App\Models\Pinjaman;
use App\Support\MemberNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentSettlement
{
    public static function settle(int $installmentId): void
    {
        DB::transaction(function () use ($installmentId): void {
            $installment = Cicilan::query()->lockForUpdate()->findOrFail($installmentId);

            if ($installment->status === 'terbayar') {
                return;
            }

            if ($installment->status !== 'belumbayar' && $installment->status !== 'denda') {
                throw ValidationException::withMessages([
                    'cicilan' => 'Status cicilan tidak dapat diselesaikan.',
                ]);
            }

            $loan = Pinjaman::query()->lockForUpdate()->findOrFail($installment->id_pinjaman);
            $installment->update([
                'status' => 'terbayar',
                'tgl_bayar' => now()->toDateString(),
            ]);

            $remaining = max((float) $loan->sisa_pinjaman - (float) $installment->nominal, 0);
            $unpaidCount = Cicilan::query()
                ->where('id_pinjaman', $loan->id)
                ->where('status', '!=', 'terbayar')
                ->count();

            $loan->update([
                'sisa_pinjaman' => $remaining,
                'cicilan_ke' => max((int) $loan->cicilan_ke, (int) $installment->cicilan_ke),
                'status' => $unpaidCount === 0 ? 'lunas' : 'aktif',
            ]);

            MemberNotifier::send(
                $installment->anggota,
                'Pembayaran cicilan berhasil',
                "Pembayaran cicilan ke-{$installment->cicilan_ke} telah diterima.",
                'cicilan.paid',
                '/anggota/cicilan',
            );
        });
    }
}
