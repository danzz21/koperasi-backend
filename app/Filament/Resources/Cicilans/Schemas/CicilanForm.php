<?php

namespace App\Filament\Resources\Cicilans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CicilanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('id_pinjaman')
                    ->label('Pinjaman')
                    ->relationship('pinjaman', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('id_anggota')
                    ->label('Anggota')
                    ->relationship('anggota', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('cicilan_ke')
                    ->required()
                    ->numeric(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                DatePicker::make('tgl_tempo')
                    ->required(),
                DatePicker::make('tgl_bayar'),
                TextInput::make('denda')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(['terbayar' => 'Terbayar', 'belumbayar' => 'Belumbayar', 'denda' => 'Denda'])
                    ->default('belumbayar')
                    ->required(),
            ]);
    }
}
