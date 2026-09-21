<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passkey extends Model
{
    protected $table = 'passkeys';

    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'counter',
        'device_name',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'counter'      => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
