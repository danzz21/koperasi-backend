<?php

namespace App\Filament\Pages;

use App\Services\PendingApprovalActions;
use App\Support\PendingApprovals;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PendingSimpananWajib extends PendingApprovalsPage
{
    protected static ?string $title = 'Daftar Setoran Simpanan Wajib Pending';

    protected static ?string $navigationLabel = 'Simpanan Wajib Pending';

    protected static ?string $slug = 'pending/simpanan-wajib';

    public static function pendingType(): string
    {
        return PendingApprovalActions::WAJIB;
    }

    public static function pendingQuery(): Builder
    {
        return PendingApprovals::wajib();
    }

    public function getSubheading(): ?string
    {
        return 'Verifikasi setoran simpanan wajib anggota.';
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('anggota.nama_lengkap')->label('Nama Anggota')->searchable()->sortable()->weight('bold'),
            TextColumn::make('nominal')->label('Jumlah Setoran')->money('IDR', locale: 'id')->sortable()->weight('bold'),
            TextColumn::make('keterangan')->label('Keterangan')->placeholder('—')->wrap(),
            TextColumn::make('created_at')->label('Tanggal Setor')->dateTime('d M Y H:i')->sortable(),
        ];
    }
}
