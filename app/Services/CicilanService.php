<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class CicilanService
{
    /**
     * @return array<string, mixed>
     */
    public function forMember(User $user): array
    {
        $loans = DB::table('pinjamen')->where('id_anggota', $user->id)->get();
        $payments = DB::table('pembayarans')->where('id_anggota', $user->id)
            ->orderByDesc('created_at')->get();
        $schedule = DB::table('cicilans')->where('id_anggota', $user->id)
            ->orderBy('cicilan_ke')->get();
        $activeLoans = $loans->where('status', 'aktif');

        return [
            'anggota' => $user,
            'pinjaman_aktif' => $activeLoans->values(),
            'pinjaman_lunas' => $loans->where('status', 'lunas')->values(),
            'pembayaran_pending' => $payments->where('status', 'pending')->values(),
            'data' => $schedule,
            'riwayat_pembayaran' => $payments,
            'summary' => [
                'total_pinjaman_aktif' => $activeLoans->count(),
                'total_sisa_kewajiban' => (float) $activeLoans->sum('sisa_pinjaman'),
            ],
        ];
    }

    /**
     * @param  array{id_pinjaman: int|string, nominal: int|float|string, metode?: string|null}  $data
     * @return array{status: int, message: string}
     */
    public function pay(User $user, array $data): array
    {
        $loan = DB::table('pinjamen')->where('id', $data['id_pinjaman'])
            ->where('id_anggota', $user->id)->where('status', 'aktif')->first();
        if (! $loan) {
            return ['status' => 404, 'message' => 'Pinjaman tidak ditemukan.'];
        }

        $installment = DB::table('cicilans')->where('id_pinjaman', $loan->id)
            ->where('id_anggota', $user->id)->where('status', 'belumbayar')
            ->orderBy('cicilan_ke')->first();
        if (! $installment || (float) $installment->nominal !== (float) $data['nominal']) {
            return [
                'status' => 422,
                'message' => 'Nominal harus sama dengan cicilan berikutnya yang belum dibayar.',
            ];
        }

        DB::table('pembayarans')->insert([
            'id_anggota' => $user->id,
            'id_cicilan' => $installment->id,
            'no_referensi' => 'CIC-'.now()->format('ymdHis').random_int(100, 999),
            'jenis' => 'cicilan',
            'nominal' => $data['nominal'],
            'metode' => $data['metode'] ?? 'transfer',
            'status' => 'pending',
            'keterangan' => 'Pembayaran cicilan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => 201, 'message' => 'Pembayaran berhasil dikirim.'];
    }

    public function history(User $user)
    {
        return DB::table('pembayarans')->where('id_anggota', $user->id)
            ->where('jenis', 'cicilan')->orderByDesc('created_at')->get();
    }
}
