<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPembayarans extends ListRecords
{
    protected static string $resource = PembayaranResource::class;

    public function getTitle(): string
    {
        return 'Manajemen Pembayaran';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola dan verifikasi transaksi pembayaran cicilan, simpanan, dan PPOB anggota';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Catat Pembayaran')
                ->icon('heroicon-m-credit-card'),
        ];
    }
}
