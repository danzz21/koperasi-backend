<?php

namespace App\Filament\Resources\Superadmin\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->icon('heroicon-m-clock')
                    ->iconColor('gray'),

                TextColumn::make('actor_name')
                    ->label('Pelaku')
                    ->searchable(['user_id'])
                    ->weight('bold')
                    ->description(fn ($record): string => $record->user?->role ?? '-'),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color('warning')
                    ->searchable(),

                TextColumn::make('question_type')
                    ->label('Objek')
                    ->placeholder('-')
                    ->description(fn ($record): ?string => $record->record_id
                        ? "ID #{$record->record_id}"
                        : null),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->icon('heroicon-m-globe-alt')
                    ->iconColor('gray')
                    ->searchable(),

                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options(fn (): array => \App\Models\AuditLog::query()
                        ->select('action')
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->all())
                    ->searchable(),

                SelectFilter::make('question_type')
                    ->label('Objek')
                    ->options(fn (): array => \App\Models\AuditLog::query()
                        ->whereNotNull('question_type')
                        ->select('question_type')
                        ->distinct()
                        ->orderBy('question_type')
                        ->pluck('question_type', 'question_type')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Waktu')
                            ->dateTime('d F Y, H:i:s'),
                        TextEntry::make('actor_name')
                            ->label('Pelaku'),
                        TextEntry::make('action')
                            ->label('Aksi'),
                        TextEntry::make('question_type')
                            ->label('Objek')
                            ->placeholder('-'),
                        TextEntry::make('record_id')
                            ->label('ID Objek')
                            ->placeholder('-'),
                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->placeholder('-'),
                        TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('old_values')
                            ->label('Nilai Sebelum')
                            ->formatStateUsing(fn ($state): string => $state
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '-')
                            ->columnSpanFull(),
                        TextEntry::make('new_values')
                            ->label('Nilai Sesudah')
                            ->formatStateUsing(fn ($state): string => $state
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '-')
                            ->columnSpanFull(),
                    ])
                    ->modalHeading('Detail Log Aktivitas'),
            ])
            ->striped()
            ->paginated([25, 50, 100]);
    }
}