<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PinjamanService
{
    public function forMember(User $user)
    {
        return DB::table('pinjamen')->where('id_anggota', $user->id)
            ->orderByDesc('created_at')->get();
    }

    /**
     * @param  array{tipe: string, nominal: int|float|string, tenor: int|string, keterangan?: string|null}  $data
     */
    public function submit(User $user, array $data): int
    {
        return DB::transaction(function () use ($data, $user): int {
            User::query()->lockForUpdate()->findOrFail($user->id);

            $hasOpenLoan = DB::table('pinjamen')
                ->where('id_anggota', $user->id)
                ->whereIn('status', ['pending', 'disetujui', 'aktif'])
                ->exists();

            if ($hasOpenLoan) {
                throw ValidationException::withMessages([
                    'pinjaman' => 'Selesaikan pengajuan atau pinjaman aktif sebelum mengajukan pinjaman baru.',
                ]);
            }

            $data['id_anggota'] = $user->id;
            $data['sisa_pinjaman'] = $data['nominal'];
            $data['angsuran'] = $data['nominal'] / $data['tenor'];
            $data['status'] = 'pending';

            return DB::table('pinjamen')->insertGetId($data + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function activeLoan(User $user)
    {
        return DB::table('pinjamen')->where('id_anggota', $user->id)
            ->where('status', 'aktif')->first();
    }
}
