<?php

namespace App\Filament\Resources\Superadmin\Users\Pages;

use App\Filament\Resources\Superadmin\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Kelola User Sistem';
    }

    public function getSubheading(): ?string
    {
        return 'Buat, ubah, aktifkan/nonaktifkan akun Admin & Anggota, serta atur role pengguna sistem.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah User')
                ->icon('heroicon-m-user-plus'),
        ];
    }
}