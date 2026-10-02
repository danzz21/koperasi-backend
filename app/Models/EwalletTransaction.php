<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat transaksi E-Wallet (topup masuk / pembayaran keluar).
 *
 * Kolom balance_after menyimpan saldo setelah transaksi, sehingga riwayat
 * mudah diaudit berurutan di dashboard admin.
 */
class EwalletTransaction extends Model
{
    protected $table = 'ewallet_transactions';

    protected $fillable = [
        'id_anggota',
        'tipe',
        'status',
        'nominal',
        'metode',
        'ref_id',
        'balance_after',
        'keterangan',
    ];

    protected $casts = [
        'nominal'       => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
