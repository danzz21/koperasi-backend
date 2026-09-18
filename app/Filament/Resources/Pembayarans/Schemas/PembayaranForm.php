<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PembayaranForm
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
                TextInput::make('no_referensi')
                    ->required(),
                Select::make('jenis')
                    ->options(['cicilan' => 'Cicilan', 'simpanan' => 'Simpanan', 'ppob' => 'Ppob'])
                    ->default('cicilan')
                    ->required(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Select::make('metode')
                    ->options(['transfer' => 'Transfer', 'tunai' => 'Tunai', 'e-wallet' => 'E wallet'])
                    ->default('transfer')
                    ->required(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'diverifikasi' => 'Diverifikasi',
            'selesai' => 'Selesai',
            'gagal' => 'Gagal',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('keterangan')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('tgl_pembayaran'),
            ]);
    }
}
