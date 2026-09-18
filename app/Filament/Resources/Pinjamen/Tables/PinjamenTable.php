<?php

namespace App\Filament\Resources\Pinjamen\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PinjamenTable
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

                TextColumn::make('tipe')
                    ->label('Jenis Akad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'qard'       => 'info',
                        'murabahah'  => 'primary',
                        'mudharabah' => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('nominal')
                    ->label('Nominal Pinjaman')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tenor')
                    ->label('Tenor')
                    ->numeric()
                    ->suffix(' bln')
                    ->sortable(),

                TextColumn::make('angsuran')
                    ->label('Angsuran/Bln')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('sisa_pinjaman')
                    ->label('Sisa Pinjaman')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif'   => 'success',
                        'lunas'   => 'gray',
                        'ditolak' => 'danger',
                        default   => 'warning',
                    }),

                TextColumn::make('tgl_cair')
                    ->label('Tgl Cair')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tgl_persetujuan')
                    ->label('Tgl Persetujuan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Tgl Pengajuan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipe')
                    ->label('Jenis Akad')
                    ->options([
                        'qard'       => 'Qard',
                        'murabahah'  => 'Murabahah',
                        'mudharabah' => 'Mudharabah',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif'   => 'Aktif',
                        'lunas'   => 'Lunas',
                        'ditolak' => 'Ditolak',
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
