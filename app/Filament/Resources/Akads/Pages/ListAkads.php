<?php

namespace App\Filament\Resources\Akads\Pages;

use App\Filament\Resources\Akads\AkadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAkads extends ListRecords
{
    protected static string $resource = AkadResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
