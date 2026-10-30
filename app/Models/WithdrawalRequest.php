<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'id_anggota',
        'total_simpanan',
        'total_kewajiban',
        'biaya_admin',
        'jumlah_pencairan',
        'status',
        'nomor_rekening',
        'jenis_bank',
        'atasnama_rekening',
        'catatan',
        'disetujui_pada',
        'dicairkan_pada',
    ];

    protected function casts(): array
    {
        return [
            'disetujui_pada' => 'datetime',
            'dicairkan_pada' => 'datetime',
        ];
    }

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }
}
