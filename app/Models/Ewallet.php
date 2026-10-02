<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Saldo E-Wallet anggota — terpisah dari saldo simpanan koperasi.
 */
class Ewallet extends Model
{
    protected $table = 'ewallets';

    protected $fillable = [
        'id_anggota',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_anggota');
    }
}
