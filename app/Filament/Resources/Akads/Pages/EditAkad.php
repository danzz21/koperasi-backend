<?php

namespace App\Filament\Resources\Akads\Pages;

use App\Filament\Resources\Akads\AkadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAkad extends EditRecord
{
    protected static string $resource = AkadResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
