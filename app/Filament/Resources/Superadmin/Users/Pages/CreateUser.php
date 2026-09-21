<?php

namespace App\Filament\Resources\Superadmin\Users\Pages;

use App\Filament\Resources\Superadmin\Users\UserResource;
use App\Support\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Tambah User Baru';
    }

    protected function afterCreate(): void
    {
        AuditLogger::log(
            action: 'user.create',
            subject: $this->record,
            newValues: $this->record->only(['username', 'email', 'role', 'status']),
        );
    }
}