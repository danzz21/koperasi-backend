<?php

namespace App\Filament\Resources\Akads\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AkadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('Kode Pengaturan')->required()->unique(ignoreRecord: true),
            TextInput::make('value')->label('Nilai / Rate')->required(),
            Textarea::make('keterangan')->columnSpanFull(),
        ]);
    }
}
