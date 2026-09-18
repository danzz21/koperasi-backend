<?php

namespace App\Filament\Resources\Transaksis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransaksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Anggota')
                ->relationship('user', 'nama_lengkap')
                ->searchable()
                ->preload(),
            Select::make('jenis')
                ->options(['simpanan' => 'Simpanan', 'pinjaman' => 'Pinjaman', 'angsuran' => 'Angsuran', 'ppob' => 'PPOB'])
                ->required(),
            Select::make('tipe')
                ->label('Arus')
                ->options(['kredit' => 'Pemasukan', 'debet' => 'Pengeluaran'])
                ->required(),
            TextInput::make('jumlah')->numeric()->prefix('Rp')->required(),
            TextInput::make('referensi')->label('Referensi'),
            DatePicker::make('tanggal')->required()->default(now()),
            Textarea::make('keterangan')->columnSpanFull(),
        ]);
    }
}
