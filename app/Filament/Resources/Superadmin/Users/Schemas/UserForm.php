<?php

namespace App\Filament\Resources\Superadmin\Users\Schemas;

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
                TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),

                TextInput::make('username')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Kosongkan bila tidak ingin mengubah password.'),

                Select::make('role')
                    ->label('Role / Hak Akses')
                    ->options([
                        'superadmin' => 'Superadmin',
                        'admin'      => 'Admin',
                        'anggota'    => 'Anggota',
                    ])
                    ->default('anggota')
                    ->required()
                    ->native(false),

                Select::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'aktif'    => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ])
                    ->default('aktif')
                    ->required()
                    ->native(false),

                TextInput::make('nomor_ktp')
                    ->label('Nomor KTP')
                    ->maxLength(16)
                    ->unique(ignoreRecord: true),

                TextInput::make('nomor_hp')
                    ->label('Nomor HP')
                    ->maxLength(20),

                TextInput::make('nomor_hp_keluarga')
                    ->label('Nomor HP Keluarga')
                    ->default(null),

                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                    ->native(false),

                Textarea::make('alamat')
                    ->default(null)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
