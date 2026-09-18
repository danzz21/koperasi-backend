<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpobTransaksi extends Model
{
    protected $table = 'ppob_transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_anggota',
        'jenis_produk',
        'provider',
        'nomor_tujuan',
        'nominal',
        'harga',
        'kode_produk',
        'nama_produk',
        'status',
        'ref_id',
        'payment_ref',
        'payment_method',
        'keterangan',
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }
}
