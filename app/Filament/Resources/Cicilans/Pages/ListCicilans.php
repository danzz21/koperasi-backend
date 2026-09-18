<?php

namespace App\Filament\Resources\Cicilans\Pages;

use App\Filament\Resources\Cicilans\CicilanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCicilans extends ListRecords
{
    protected static string $resource = CicilanResource::class;

    public function getTitle(): string
    {
        return 'Manajemen Angsuran';
    }

    public function getSubheading(): ?string
    {
        return 'Pantau status cicilan dan angsuran pinjaman seluruh anggota koperasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Catat Angsuran')
                ->icon('heroicon-m-calendar-days'),
        ];
    }
}
