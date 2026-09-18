<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpanan extends Model
{
    protected $fillable = [
        'id_anggota',
        'jenis',
        'nominal',
        'bunga',
        'keterangan',
        'status',
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }
}
