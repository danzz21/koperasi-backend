<?php

namespace App\Filament\Resources\Cicilans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CicilansTable
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

                TextColumn::make('pinjaman.id')
                    ->label('Pinjaman Ke')
                    ->sortable()
                    ->prefix('#'),

                TextColumn::make('cicilan_ke')
                    ->label('Cicilan Ke')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('denda')
                    ->label('Denda')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color(fn ($state): string => ($state > 0) ? 'danger' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tgl_tempo')
                    ->label('Tgl Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days'),

                TextColumn::make('tgl_bayar')
                    ->label('Tgl Bayar')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('Belum dibayar'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'terbayar'   => 'success',
                        'belumbayar' => 'warning',
                        'denda'      => 'danger',
                        default      => 'gray',
                    }),
            ])
            ->defaultSort('tgl_tempo', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'terbayar'   => 'Terbayar',
                        'belumbayar' => 'Belum Bayar',
                        'denda'      => 'Denda',
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
