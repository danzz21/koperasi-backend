<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SimpananService
{
    /**
     * @return array<string, mixed>
     */
    public function forMember(User $user): array
    {
        $rows = DB::table('simpanans')->where('id_anggota', $user->id)
            ->orderByDesc('created_at')->get();
        $active = $rows->where('status', 'aktif');
        $summary = $active->groupBy('jenis')->map(fn ($items) => (float) $items->sum('nominal'));

        return [
            'anggota' => $user,
            'tenor' => null,
            'show_tenor_modal' => false,
            'data' => [
                'sim_pokok' => $summary->get('pokok', 0),
                'sim_wajib' => $summary->get('wajib', 0),
                'sim_sukarela' => $summary->get('sukarela', 0),
                'target_wajib' => 100000,
                'bunga_sukarela' => 0,
                'tgl_sim_pokok' => optional($rows->firstWhere('jenis', 'pokok'))->created_at,
                'items' => $rows,
            ],
            'simpanan' => $rows,
            'summary' => [
                'total_pokok' => $summary->get('pokok', 0),
                'total_wajib' => $summary->get('wajib', 0),
                'total_sukarela' => $summary->get('sukarela', 0),
                'total' => (float) $active->sum('nominal'),
            ],
        ];
    }

    public function setTenor(int $tenor): int
    {
        return $tenor;
    }

    /**
     * @param  array{jumlah: int|float|string, keterangan?: string|null}  $data
     */
    public function store(User $user, string $jenis, array $data): bool
    {
        $pendingWithdrawal = DB::table('withdrawal_requests')
            ->where('id_anggota', $user->id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->exists();

        if ($pendingWithdrawal) {
            return false;
        }

        DB::table('simpanans')->insert([
            'id_anggota' => $user->id,
            'jenis' => $jenis,
            'nominal' => $data['jumlah'],
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'tidak_aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }
}
