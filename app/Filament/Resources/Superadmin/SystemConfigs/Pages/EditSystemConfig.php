<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs\Pages;

use App\Filament\Resources\Superadmin\SystemConfigs\Concerns\DecodesJsonValue;
use App\Filament\Resources\Superadmin\SystemConfigs\SystemConfigResource;
use App\Support\AuditLogger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSystemConfig extends EditRecord
{
    use DecodesJsonValue;

    protected static string $resource = SystemConfigResource::class;

    public function getTitle(): string
    {
        return 'Ubah Konfigurasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Konfigurasi'),
        ];
    }

    protected function afterSave(): void
    {
        AuditLogger::log(
            action: 'system_config.update',
            subject: $this->record,
            newValues: ['key' => $this->record->key, 'value' => $this->record->value],
        );
    }
}