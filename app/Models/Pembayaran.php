<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = [
        'id_anggota',
        'no_referensi',
        'jenis',
        'nominal',
        'metode',
        'status',
        'keterangan',
        'tgl_pembayaran',
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }
}
