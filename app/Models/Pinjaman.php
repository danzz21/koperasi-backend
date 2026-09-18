<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
