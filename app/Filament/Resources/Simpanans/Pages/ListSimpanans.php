<?php

namespace App\Filament\Resources\Simpanans\Pages;

use App\Filament\Resources\Simpanans\SimpananResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSimpanans extends ListRecords
{
    protected static string $resource = SimpananResource::class;

    public function getTitle(): string
    {
        return 'Manajemen Simpanan';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola simpanan pokok, wajib, dan sukarela seluruh anggota koperasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Input Simpanan')
                ->icon('heroicon-m-plus-circle'),
        ];
    }
}
