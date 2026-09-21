<?php

namespace App\Filament\Resources\Superadmin\Users\Tables;

use App\Support\AuditLogger;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): string => $record->username ?? ''),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('gray')
                    ->copyable(),

                TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->iconColor('gray'),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin'      => 'primary',
                        'anggota'    => 'info',
                        default      => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif'    => 'success',
                        'pending'  => 'warning',
                        'nonaktif' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'superadmin' => 'Superadmin',
                        'admin'      => 'Admin',
                        'anggota'    => 'Anggota',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'aktif'    => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square'),

                Action::make('ubahRole')
                    ->label('Ubah Role')
                    ->icon('heroicon-m-shield-check')
                    ->color('warning')
                    ->schema([
                        Select::make('role')
                            ->label('Role Baru')
                            ->options([
                                'superadmin' => 'Superadmin',
                                'admin'      => 'Admin',
                                'anggota'    => 'Anggota',
                            ])
                            ->default(fn ($record) => $record->role)
                            ->required()
                            ->native(false),
                    ])
                    ->action(function ($record, array $data): void {
                        $old = $record->role;
                        $record->update(['role' => $data['role']]);

                        AuditLogger::log(
                            action: 'user.update_role',
                            subject: $record,
                            oldValues: ['role' => $old],
                            newValues: ['role' => $data['role']],
                        );

                        Notification::make()
                            ->title('Role berhasil diubah')
                            ->body("{$record->nama_lengkap}: {$old} → {$data['role']}")
                            ->success()
                            ->send();
                    }),

                Action::make('toggleStatus')
                    ->label(fn ($record): string => $record->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record): string => $record->status === 'aktif'
                        ? 'heroicon-m-lock-closed'
                        : 'heroicon-m-lock-open')
                    ->color(fn ($record): string => $record->status === 'aktif' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $old = $record->status;
                        $new = $old === 'aktif' ? 'nonaktif' : 'aktif';
                        $record->update(['status' => $new]);

                        AuditLogger::log(
                            action: 'user.toggle_status',
                            subject: $record,
                            oldValues: ['status' => $old],
                            newValues: ['status' => $new],
                        );

                        Notification::make()
                            ->title("Status {$record->nama_lengkap} diubah menjadi {$new}")
                            ->success()
                            ->send();
                    }),
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
