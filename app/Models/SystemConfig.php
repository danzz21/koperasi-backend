<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $table = 'system_configs';

    protected $fillable = ['key', 'value', 'label', 'description'];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Ambil nilai konfigurasi berdasarkan key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = static::query()->where('key', $key)->first();

        return $config?->value ?? $default;
    }

    /**
     * Simpan / update nilai konfigurasi.
     */
    public static function setValue(
        string $key,
        mixed $value,
        ?string $label = null,
        ?string $description = null,
    ): self {
        return static::query()->updateOrCreate(
            ['key' => $key],
            array_filter([
                'value'       => $value,
                'label'       => $label,
                'description' => $description,
            ], fn ($item) => $item !== null)
        );
    }
}
