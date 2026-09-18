<?php

namespace App\Filament\Resources\PpobTransaksis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PpobTransaksisTable
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

                TextColumn::make('jenis_produk')
                    ->label('Jenis Layanan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pulsa'         => 'primary',
                        'paket_data'    => 'info',
                        'token_listrik' => 'warning',
                        'ewallet'       => 'success',
                        default         => 'gray',
                    }),

                TextColumn::make('provider')
                    ->label('Provider')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('nomor_tujuan')
                    ->label('Nomor Tujuan')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-device-phone-mobile'),

                TextColumn::make('harga')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed'  => 'danger',
                        'pending' => 'warning',
                        default   => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('jenis_produk')
                    ->label('Jenis Layanan')
                    ->options([
                        'pulsa'         => 'Pulsa',
                        'paket_data'    => 'Paket Data',
                        'token_listrik' => 'Token Listrik',
                        'ewallet'       => 'E-Wallet',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Berhasil',
                        'failed'  => 'Gagal',
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
