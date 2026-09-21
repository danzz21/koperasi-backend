<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs;

use App\Filament\Resources\Superadmin\Concerns\OnlyInSuperadminPanel;
use App\Filament\Resources\Superadmin\SystemConfigs\Pages\CreateSystemConfig;
use App\Filament\Resources\Superadmin\SystemConfigs\Pages\EditSystemConfig;
use App\Filament\Resources\Superadmin\SystemConfigs\Pages\ListSystemConfigs;
use App\Filament\Resources\Superadmin\SystemConfigs\Schemas\SystemConfigForm;
use App\Filament\Resources\Superadmin\SystemConfigs\Tables\SystemConfigsTable;
use App\Models\SystemConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SystemConfigResource extends Resource
{
    use OnlyInSuperadminPanel;

    protected static ?string $model = SystemConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Konfigurasi Sistem';

    protected static ?string $modelLabel = 'Konfigurasi';

    protected static ?string $pluralModelLabel = 'Konfigurasi Sistem';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Sistem';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'key';

    protected static ?string $slug = 'system-configs';

    public static function form(Schema $schema): Schema
    {
        return SystemConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemConfigs::route('/'),
            'create' => CreateSystemConfig::route('/create'),
            'edit' => EditSystemConfig::route('/{record}/edit'),
        ];
    }
}
