<?php

namespace App\Filament\Resources\Pinjamen\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PinjamanForm
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
                Select::make('tipe')
                    ->options(['qard' => 'Qard', 'murabahah' => 'Murabahah', 'mudharabah' => 'Mudharabah'])
                    ->default('qard')
                    ->required(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('sisa_pinjaman')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('tenor')
                    ->required()
                    ->numeric()
                    ->suffix('bulan'),
                TextInput::make('bunga_per_bulan')
                    ->numeric()
                    ->default(null),
                TextInput::make('angsuran')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('cicilan_ke')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(['aktif' => 'Aktif', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak'])
                    ->default('aktif')
                    ->required(),
                DatePicker::make('tgl_persetujuan'),
                DatePicker::make('tgl_cair'),
                Textarea::make('keterangan')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
