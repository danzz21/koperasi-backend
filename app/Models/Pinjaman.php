<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    protected $fillable = [
        'id_anggota',
        'tipe',
        'nominal',
        'sisa_pinjaman',
        'tenor',
        'bunga_per_bulan',
        'angsuran',
        'cicilan_ke',
        'status',
        'tgl_persetujuan',
        'tgl_cair',
        'keterangan',
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }

    public function cicilans(): HasMany
    {
        return $this->hasMany(Cicilan::class, 'id_pinjaman');
    }
}
