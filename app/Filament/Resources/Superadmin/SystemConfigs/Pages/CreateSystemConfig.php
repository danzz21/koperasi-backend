<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs\Pages;

use App\Filament\Resources\Superadmin\SystemConfigs\Concerns\DecodesJsonValue;
use App\Filament\Resources\Superadmin\SystemConfigs\SystemConfigResource;
use App\Support\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemConfig extends CreateRecord
{
    use DecodesJsonValue;

    protected static string $resource = SystemConfigResource::class;

    public function getTitle(): string
    {
        return 'Tambah Konfigurasi';
    }

    protected function afterCreate(): void
    {
        AuditLogger::log(
            action: 'system_config.create',
            subject: $this->record,
            newValues: ['key' => $this->record->key, 'value' => $this->record->value],
        );
    }
}