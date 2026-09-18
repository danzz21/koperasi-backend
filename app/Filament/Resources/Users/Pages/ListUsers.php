<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Manajemen Anggota';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola data anggota aktif, berkas dokumen, dan pendaftaran anggota baru koperasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Anggota')
                ->icon('heroicon-m-user-plus'),
        ];
    }
}
