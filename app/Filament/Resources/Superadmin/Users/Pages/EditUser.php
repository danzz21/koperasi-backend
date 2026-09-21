<?php

namespace App\Filament\Resources\Superadmin\Users\Pages;

use App\Filament\Resources\Superadmin\Users\UserResource;
use App\Support\AuditLogger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Ubah Data User';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus User'),
        ];
    }

    protected function afterSave(): void
    {
        AuditLogger::log(
            action: 'user.update',
            subject: $this->record,
            oldValues: collect($this->record->getOriginal())
                ->only(['username', 'email', 'role', 'status'])
                ->all(),
            newValues: $this->record->only(['username', 'email', 'role', 'status']),
        );
    }
}