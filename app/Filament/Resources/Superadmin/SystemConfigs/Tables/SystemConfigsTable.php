<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SystemConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->icon('heroicon-m-key')
                    ->iconColor('gray'),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(fn ($state): string => is_array($state)
                        ? json_encode($state, JSON_UNESCAPED_UNICODE)
                        : (string) $state)
                    ->limit(50)
                    ->tooltip(fn ($record): string => is_array($record->value)
                        ? json_encode($record->value, JSON_UNESCAPED_UNICODE)
                        : (string) $record->value),

                TextColumn::make('label')
                    ->label('Label')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-m-arrow-path')
                    ->iconColor('gray'),
            ])
            ->defaultSort('key', 'asc')
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-m-pencil-square'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
