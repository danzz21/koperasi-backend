<?php

namespace App\Filament\Pages;

use App\Support\PendingApprovalActions;
use App\Support\PendingApprovals;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pembayaran cicilan yang menunggu verifikasi admin.
 * Diakses dari kartu "Pembayaran Cicilan" di dashboard admin.
 */
class PendingPembayaran extends PendingApprovalsPage
{
    protected static ?string $title = 'Daftar Pembayaran Cicilan Pending';

    protected static ?string $navigationLabel = 'Pembayaran Pending';

    protected static ?string $slug = 'pending/pembayaran';

    public static function pendingType(): string
    {
        return PendingApprovalActions::PEMBAYARAN;
    }

    public static function pendingQuery(): Builder
    {
        return PendingApprovals::pembayaran();
    }

    public function getSubheading(): ?string
    {
        return 'Verifikasi setoran angsuran yang dikirim anggota.';
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('no_referensi')
                ->label('No. Referensi')
                ->searchable()
                ->copyable()
                ->weight('bold'),

            TextColumn::make('anggota.nama_lengkap')
                ->label('Nama Anggota')
                ->searchable()
                ->sortable()
                ->icon('heroicon-m-user')
                ->iconColor('gray'),

            TextColumn::make('nominal')
                ->label('Jumlah')
                ->money('IDR', locale: 'id')
                ->sortable()
                ->weight('bold')
                ->color('success'),

            TextColumn::make('metode')
                ->label('Metode')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'transfer'  => 'Transfer Bank',
                    'tunai'     => 'Tunai',
                    'e-wallet'  => 'E-Wallet',
                    default     => (string) $state,
                }),

            TextColumn::make('created_at')
                ->label('Tgl Kirim')
                ->date('d M Y H:i')
                ->sortable()
                ->icon('heroicon-m-calendar-days')
                ->iconColor('gray'),
        ];
    }
}
