<?php

namespace App\Filament\Pages;

use App\Services\PendingApprovalActions;
use App\Support\PendingApprovals;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pengajuan pinjaman (akad) yang menunggu persetujuan admin.
 * Diakses dari kartu "Pengajuan Pinjaman" di dashboard admin.
 */
class PendingPinjaman extends PendingApprovalsPage
{
    protected static ?string $title = 'Daftar Pengajuan Pinjaman Pending';

    protected static ?string $navigationLabel = 'Pinjaman Pending';

    protected static ?string $slug = 'pending/pinjaman';

    public static function pendingType(): string
    {
        return PendingApprovalActions::PINJAMAN;
    }

    public static function pendingQuery(): Builder
    {
        return PendingApprovals::pinjaman();
    }

    public function getSubheading(): ?string
    {
        return 'Periksa pengajuan pembiayaan anggota sebelum menyetujui akad.';
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

            TextColumn::make('tipe')
                ->label('Akad')
                ->badge()
                ->formatStateUsing(fn (string $state): string => strtoupper($state))
                ->color(fn (string $state): string => match ($state) {
                    'qard'      => 'info',
                    'murabahah' => 'primary',
                    'mudharabah' => 'warning',
                    default     => 'gray',
                }),

            TextColumn::make('nominal')
                ->label('Nominal')
                ->money('IDR', locale: 'id')
                ->sortable()
                ->weight('bold')
                ->color('success'),

            TextColumn::make('tenor')
                ->label('Tenor')
                ->suffix(' bulan')
                ->sortable(),

            TextColumn::make('angsuran')
                ->label('Angsuran')
                ->money('IDR', locale: 'id')
                ->placeholder('—'),

            TextColumn::make('keterangan')
                ->label('Keterangan')
                ->placeholder('—')
                ->wrap()
                ->limit(40)
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Tgl Pengajuan')
                ->date('d M Y H:i')
                ->sortable()
                ->icon('heroicon-m-calendar-days')
                ->iconColor('gray'),
        ];
    }
}
