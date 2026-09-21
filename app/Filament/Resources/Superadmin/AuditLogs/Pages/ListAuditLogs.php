<?php

namespace App\Filament\Resources\Superadmin\AuditLogs\Pages;

use App\Filament\Resources\Superadmin\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    public function getTitle(): string
    {
        return 'Log Aktivitas Sistem';
    }

    public function getSubheading(): ?string
    {
        return 'Jejak digital (audit trail) seluruh tindakan yang dilakukan Admin maupun Anggota.';
    }
}
