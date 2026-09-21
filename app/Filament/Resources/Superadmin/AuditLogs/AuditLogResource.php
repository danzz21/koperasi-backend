<?php

namespace App\Filament\Resources\Superadmin\AuditLogs;

use App\Filament\Resources\Superadmin\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Resources\Superadmin\AuditLogs\Tables\AuditLogsTable;
use App\Filament\Resources\Superadmin\Concerns\OnlyInSuperadminPanel;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AuditLogResource extends Resource
{
    use OnlyInSuperadminPanel;

    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static string|UnitEnum|null $navigationGroup = 'Keamanan & Audit';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'audit-logs';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AuditLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
        ];
    }
}
