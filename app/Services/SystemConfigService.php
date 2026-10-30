<?php

namespace App\Services;

use App\Models\SystemConfig;
use App\Support\AuditLogger;

class SystemConfigService
{
    public function listing(?string $search)
    {
        $query = SystemConfig::query();

        if ($search !== null && $search !== '') {
            $query->where(function ($subquery) use ($search) {
                $subquery->where('key', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('key')->get();
    }

    public function find(string $key): ?SystemConfig
    {
        return SystemConfig::query()->where('key', $key)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SystemConfig
    {
        $config = SystemConfig::setValue(
            $data['key'],
            $data['value'],
            $data['label'] ?? null,
            $data['description'] ?? null,
        );

        AuditLogger::log('system_config.create', $config, [], [
            'key' => $config->key,
            'value' => $config->value,
        ]);

        return $config;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $key, array $data): SystemConfig
    {
        $config = $this->find($key);
        $oldValue = $config?->value;
        $config = SystemConfig::setValue(
            $key,
            $data['value'],
            $data['label'] ?? null,
            $data['description'] ?? null,
        );

        AuditLogger::log('system_config.update', $config, ['value' => $oldValue], [
            'key' => $config->key,
            'value' => $config->value,
        ]);

        return $config;
    }

    public function delete(int|string $id): void
    {
        $config = SystemConfig::findOrFail($id);
        AuditLogger::log('system_config.delete', $config, ['key' => $config->key, 'value' => $config->value]);
        $config->delete();
    }
}
