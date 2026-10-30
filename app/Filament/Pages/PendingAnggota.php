<?php

namespace App\Filament\Pages;

use App\Services\PendingApprovalActions;
use App\Support\PendingApprovals;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Daftar anggota baru yang menunggu verifikasi admin.
 * Diakses dari kartu "Anggota Baru" di dashboard admin.
 */
class PendingAnggota extends PendingApprovalsPage
{
    protected static ?string $title = 'Daftar Anggota Pending';

    protected static ?string $navigationLabel = 'Anggota Baru';

    protected static ?string $slug = 'pending/anggota';

    public static function pendingType(): string
    {
        return PendingApprovalActions::ANGGOTA;
    }

    public static function pendingQuery(): Builder
    {
        return PendingApprovals::anggota();
    }

    protected function approveLabel(): string
    {
        return 'Terima';
    }

    protected function rejectLabel(): string
    {
        return 'Tolak';
    }

    public function getSubheading(): ?string
    {
        return 'Verifikasi data pendaftaran anggota baru sebelum mengaktifkan akunnya.';
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('nama_lengkap')
                ->label('Nama Lengkap')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->description(fn ($record): string => (string) $record->username),

            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->copyable()
                ->icon('heroicon-m-envelope')
                ->iconColor('gray'),

            TextColumn::make('nomor_hp')
                ->label('No. HP')
                ->icon('heroicon-m-phone')
                ->iconColor('gray'),

            TextColumn::make('nomor_ktp')
                ->label('No. KTP')
                ->copyable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('google_id')
                ->label('Sumber')
                ->badge()
                ->color(fn (?string $state): string => $state ? 'info' : 'gray')
                ->formatStateUsing(fn (?string $state): string => $state ? 'Google' : 'Formulir')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Tgl Daftar')
                ->date('d M Y H:i')
                ->sortable()
                ->icon('heroicon-m-calendar-days')
                ->iconColor('gray'),
        ];
    }
}
