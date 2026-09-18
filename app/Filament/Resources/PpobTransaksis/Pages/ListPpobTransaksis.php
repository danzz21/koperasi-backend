<?php

namespace App\Filament\Resources\PpobTransaksis\Pages;

use App\Filament\Resources\PpobTransaksis\PpobTransaksiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPpobTransaksis extends ListRecords
{
    protected static string $resource = PpobTransaksiResource::class;

    public function getTitle(): string
    {
        return 'Transaksi PPOB';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola transaksi pulsa, paket data, token listrik, dan e-wallet anggota koperasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Transaksi Baru')
                ->icon('heroicon-m-device-phone-mobile'),
        ];
    }
}
