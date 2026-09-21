<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SystemConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Key')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->helperText('Contoh: murabahah_margin, min_simpanan_pokok, nama_koperasi'),

                TextInput::make('label')
                    ->label('Label Tampilan')
                    ->maxLength(150)
                    ->helperText('Nama yang muncul di UI (opsional).'),

                Textarea::make('value')
                    ->label('Nilai')
                    ->required()
                    ->rows(4)
                    ->formatStateUsing(fn ($state): string => is_array($state)
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : (string) $state)
                    ->helperText('Teks biasa atau JSON, contoh: {"margin": 10}')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->default(null)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
