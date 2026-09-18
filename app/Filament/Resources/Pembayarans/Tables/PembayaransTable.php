<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('anggota.nama_lengkap')
                    ->label('Anggota')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                TextColumn::make('no_referensi')
                    ->label('No. Referensi')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-document-text')
                    ->iconColor('gray'),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cicilan'  => 'primary',
                        'simpanan' => 'success',
                        'ppob'     => 'info',
                        default    => 'gray',
                    }),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('metode')
                    ->label('Metode')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'selesai'      => 'success',
                        'diverifikasi' => 'info',
                        'pending'      => 'warning',
                        'gagal'        => 'danger',
                        default        => 'gray',
                    }),

                TextColumn::make('tgl_pembayaran')
                    ->label('Tgl Pembayaran')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray'),
            ])
            ->defaultSort('tgl_pembayaran', 'desc')
            ->filters([
                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'cicilan'  => 'Cicilan',
                        'simpanan' => 'Simpanan',
                        'ppob'     => 'PPOB',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'      => 'Pending',
                        'diverifikasi' => 'Diverifikasi',
                        'selesai'      => 'Selesai',
                        'gagal'        => 'Gagal',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
