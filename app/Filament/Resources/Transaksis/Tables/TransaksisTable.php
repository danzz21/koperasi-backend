<?php

namespace App\Filament\Resources\Transaksis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransaksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('d M Y')->sortable(),
                TextColumn::make('user.nama_lengkap')->label('Anggota')->searchable(),
                TextColumn::make('keterangan')->label('Deskripsi')->searchable()->limit(35),
                TextColumn::make('jenis')->badge(),
                TextColumn::make('jumlah')->money('IDR', locale: 'id')->sortable(),
                TextColumn::make('tipe')->badge()->color(fn (string $state): string => $state === 'kredit' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('jenis')->options(['simpanan' => 'Simpanan', 'pinjaman' => 'Pinjaman', 'angsuran' => 'Angsuran', 'ppob' => 'PPOB']),
                SelectFilter::make('tipe')->options(['kredit' => 'Pemasukan', 'debet' => 'Pengeluaran']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
