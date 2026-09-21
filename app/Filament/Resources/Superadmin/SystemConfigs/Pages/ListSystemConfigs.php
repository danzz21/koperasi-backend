<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs\Pages;

use App\Filament\Resources\Superadmin\SystemConfigs\SystemConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSystemConfigs extends ListRecords
{
    protected static string $resource = SystemConfigResource::class;

    public function getTitle(): string
    {
        return 'Konfigurasi Sistem';
    }

    public function getSubheading(): ?string
    {
        return 'Atur parameter dasar aplikasi: margin akad, limit transaksi, identitas koperasi, dan lainnya.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Konfigurasi')
                ->icon('heroicon-m-plus'),
        ];
    }
}