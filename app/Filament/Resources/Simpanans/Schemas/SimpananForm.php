<?php

namespace App\Filament\Resources\Simpanans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SimpananForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('id_anggota')
                    ->label('Anggota')
                    ->relationship('anggota', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('jenis')
                    ->options(['pokok' => 'Pokok', 'wajib' => 'Wajib', 'sukarela' => 'Sukarela'])
                    ->default('pokok')
                    ->required(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0.0),
                TextInput::make('bunga')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('keterangan')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['aktif' => 'Aktif', 'tidak_aktif' => 'Tidak aktif'])
                    ->default('aktif')
                    ->required(),
            ]);
    }
}
