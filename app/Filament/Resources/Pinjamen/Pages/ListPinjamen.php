<?php

namespace App\Filament\Resources\Pinjamen\Pages;

use App\Filament\Resources\Pinjamen\PinjamanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPinjamen extends ListRecords
{
    protected static string $resource = PinjamanResource::class;

    public function getTitle(): string
    {
        return 'Manajemen Pinjaman';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola dan pantau seluruh pengajuan serta rincian pembiayaan anggota koperasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Pinjaman Baru')
                ->icon('heroicon-m-banknotes'),
        ];
    }
}
