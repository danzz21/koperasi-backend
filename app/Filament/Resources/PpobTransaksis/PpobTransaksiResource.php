<?php

namespace App\Filament\Resources\PpobTransaksis;

use App\Filament\Resources\PpobTransaksis\Pages\CreatePpobTransaksi;
use App\Filament\Resources\PpobTransaksis\Pages\EditPpobTransaksi;
use App\Filament\Resources\PpobTransaksis\Pages\ListPpobTransaksis;
use App\Filament\Resources\PpobTransaksis\Schemas\PpobTransaksiForm;
use App\Filament\Resources\PpobTransaksis\Tables\PpobTransaksisTable;
use App\Models\PpobTransaksi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PpobTransaksiResource extends Resource
{
    protected static ?string $model = PpobTransaksi::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;
    protected static ?string $navigationLabel = 'Transaksi PPOB';
    protected static ?string $modelLabel = 'Transaksi PPOB';
    protected static ?string $pluralModelLabel = 'Transaksi PPOB';
    protected static UnitEnum|string|null $navigationGroup = 'Manajemen Koperasi';

    public static function form(Schema $schema): Schema
    {
        return PpobTransaksiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PpobTransaksisTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPpobTransaksis::route('/'),
            'create' => CreatePpobTransaksi::route('/create'),
            'edit' => EditPpobTransaksi::route('/{record}/edit'),
        ];
    }
}
