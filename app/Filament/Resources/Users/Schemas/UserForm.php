<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('nama_lengkap')
                    ->required(),
                TextInput::make('nomor_ktp')
                    ->required()
                    ->maxLength(16),
                TextInput::make('nomor_hp')
                    ->required()
                    ->maxLength(20),
                TextInput::make('nomor_hp_keluarga')
                    ->default(null),
                Select::make('jenis_kelamin')
                    ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                    ->native(false),
                DatePicker::make('tgl_lahir'),
                TextInput::make('pekerjaan')
                    ->default(null),
                Textarea::make('alamat')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('no_rek')
                    ->default(null),
                TextInput::make('atasnama_rekening')
                    ->default(null),
                TextInput::make('jenis_bank')
                    ->default(null),
                TextInput::make('foto')
                    ->default(null)
                    ->maxLength(255),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'anggota' => 'Anggota'])
                    ->default('anggota')
                    ->required(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
