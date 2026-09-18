<?php

namespace App\Filament\Resources\Cicilans;

use App\Filament\Resources\Cicilans\Pages\CreateCicilan;
use App\Filament\Resources\Cicilans\Pages\EditCicilan;
use App\Filament\Resources\Cicilans\Pages\ListCicilans;
use App\Filament\Resources\Cicilans\Schemas\CicilanForm;
use App\Filament\Resources\Cicilans\Tables\CicilansTable;
use App\Models\Cicilan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CicilanResource extends Resource
{
    protected static ?string $model = Cicilan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Angsuran';

    protected static ?string $modelLabel = 'Angsuran';

    protected static ?string $pluralModelLabel = 'Angsuran';

    protected static UnitEnum|string|null $navigationGroup = 'Manajemen Koperasi';

    public static function form(Schema $schema): Schema
    {
        return CicilanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CicilansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCicilans::route('/'),
            'create' => CreateCicilan::route('/create'),
            'edit' => EditCicilan::route('/{record}/edit'),
        ];
    }
}
