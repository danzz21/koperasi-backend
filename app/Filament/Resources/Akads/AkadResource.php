<?php

namespace App\Filament\Resources\Akads;

use App\Filament\Resources\Akads\Pages\CreateAkad;
use App\Filament\Resources\Akads\Pages\EditAkad;
use App\Filament\Resources\Akads\Pages\ListAkads;
use App\Filament\Resources\Akads\Schemas\AkadForm;
use App\Filament\Resources\Akads\Tables\AkadsTable;
use App\Models\Akad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AkadResource extends Resource
{
    protected static ?string $model = Akad::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;
    protected static ?string $navigationLabel = 'Pengaturan Akad';
    protected static ?string $modelLabel = 'Pengaturan Akad';
    protected static ?string $pluralModelLabel = 'Pengaturan Akad';
    protected static UnitEnum|string|null $navigationGroup = 'Laporan & Sistem';

    public static function form(Schema $schema): Schema { return AkadForm::configure($schema); }
    public static function table(Table $table): Table { return AkadsTable::configure($table); }
    public static function getPages(): array
    {
        return [
            'index' => ListAkads::route('/'),
            'create' => CreateAkad::route('/create'),
            'edit' => EditAkad::route('/{record}/edit'),
        ];
    }
}
