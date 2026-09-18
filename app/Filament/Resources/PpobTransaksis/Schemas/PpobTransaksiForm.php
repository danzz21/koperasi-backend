<?php

namespace App\Filament\Resources\PpobTransaksis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PpobTransaksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('id_anggota')
                ->label('Anggota')
                ->relationship('anggota', 'nama_lengkap')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('jenis_produk')
                ->label('Jenis Produk')
                ->options([
                    'pulsa' => 'Pulsa',
                    'paket_data' => 'Paket Data',
                    'token_listrik' => 'Token Listrik',
                    'ewallet' => 'E-Wallet',
                ])
                ->required(),
            TextInput::make('provider')->required(),
            TextInput::make('nomor_tujuan')->label('Nomor Tujuan')->required(),
            TextInput::make('nominal')->numeric()->prefix('Rp')->required(),
            TextInput::make('harga')->numeric()->prefix('Rp')->required(),
            TextInput::make('kode_produk')->required(),
            TextInput::make('nama_produk')->required(),
            Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'success' => 'Berhasil',
                    'failed' => 'Gagal',
                ])
                ->required(),
            TextInput::make('ref_id'),
            TextInput::make('payment_ref'),
            TextInput::make('payment_method'),
            Textarea::make('keterangan')->columnSpanFull(),
        ]);
    }
}
