<?php

namespace App\Filament\Resources\PpobTransaksis\Pages;

use App\Filament\Resources\PpobTransaksis\PpobTransaksiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPpobTransaksi extends EditRecord
{
    protected static string $resource = PpobTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
