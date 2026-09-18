<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cicilan extends Model
{
    protected $fillable = [
        'id_pinjaman',
        'id_anggota',
        'cicilan_ke',
        'nominal',
        'tgl_tempo',
        'tgl_bayar',
        'denda',
        'status',
    ];

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'id_pinjaman');
    }

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }
}
