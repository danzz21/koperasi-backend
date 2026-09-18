<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table      = 'anggota';
    protected $primaryKey = 'id_anggota';

    protected $fillable = [
        'id_anggota', 'id_user', 'nomor_anggota',
        'nama_lengkap', 'email', 'no_ktp', 'no_hp',
        'alamat', 'jenis_kelamin', 'pekerjaan',
        'no_rek', 'atasnama_rekening', 'jenis_bank',
        'foto_diri', 'foto_ktp', 'foto_diri_ktp',
        'photo', 'status', 'tanggal_daftar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_anggota', 'id');
    }
}
