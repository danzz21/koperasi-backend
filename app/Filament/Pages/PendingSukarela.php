<?php

namespace App\Filament\Pages;

use App\Support\PendingApprovalActions;
use App\Support\PendingApprovals;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Setoran simpanan sukarela yang menunggu konfirmasi admin.
 * Diakses dari kartu "Simpanan Sukarela" di dashboard admin.
 */
class PendingSukarela extends PendingApprovalsPage
{
    protected static ?string $title = 'Daftar Setoran Sukarela Pending';

    protected static ?string $navigationLabel = 'Sukarela Pending';

    protected static ?string $slug = 'pending/sukarela';

    public static function pendingType(): string
    {
        return PendingApprovalActions::SUKARELA;
    }

    public static function pendingQuery(): Builder
    {
        return PendingApprovals::sukarela();
    }

    public function getSubheading(): ?string
    {
        return 'Konfirmasi setoran simpanan sukarela yang dikirim anggota.';
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('anggota.nama_lengkap')
                ->label('Nama Anggota')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->icon('heroicon-m-user')
                ->iconColor('gray'),

            TextColumn::make('nominal')
                ->label('Jumlah Setoran')
                ->money('IDR', locale: 'id')
                ->sortable()
                ->weight('bold')
                ->color('success'),

            TextColumn::make('keterangan')
                ->label('Keterangan')
                ->placeholder('—')
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => 'Menunggu verifikasi'),

            TextColumn::make('created_at')
                ->label('Tanggal Setor')
                ->date('d M Y H:i')
                ->sortable()
                ->icon('heroicon-m-calendar-days')
                ->iconColor('gray'),
        ];
    }
}
