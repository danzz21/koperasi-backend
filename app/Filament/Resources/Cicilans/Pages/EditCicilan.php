<?php

namespace App\Filament\Resources\Cicilans\Pages;

use App\Filament\Resources\Cicilans\CicilanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCicilan extends EditRecord
{
    protected static string $resource = CicilanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
