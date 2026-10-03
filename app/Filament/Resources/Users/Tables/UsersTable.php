<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\MemberAccountActions;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
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
                        'admin'    => 'primary',
                        'anggota'  => 'info',
                        default    => 'gray',
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
                    ->options(['admin' => 'Admin', 'anggota' => 'Anggota']),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'aktif'    => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-m-eye'),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square'),

                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-m-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset password anggota?')
                    ->modalDescription('Password diubah ke "'.MemberAccountActions::DEFAULT_PASSWORD.'" dan anggota harus login ulang di semua perangkat.')
                    ->modalSubmitActionLabel('Ya, reset')
                    ->action(function (User $record): void {
                        MemberAccountActions::resetPassword($record);

                        Notification::make()
                            ->warning()
                            ->title('Password direset')
                            ->body('Password '.$record->nama_lengkap.' sekarang "'.MemberAccountActions::DEFAULT_PASSWORD.'". '
                                .'Minta anggota segera menggantinya.')
                            ->send();
                    }),

                Action::make('nonaktifkan')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan anggota ini?')
                    ->modalDescription('Anggota tidak bisa login sampai diaktifkan kembali.')
                    ->visible(fn (User $record): bool => $record->status === 'aktif')
                    ->action(function (User $record): void {
                        MemberAccountActions::setActive($record, false);

                        Notification::make()->danger()->title('Akun dinonaktifkan')->send();
                    }),

                Action::make('aktifkan')
                    ->label('Aktifkan')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan anggota ini?')
                    ->visible(fn (User $record): bool => $record->status !== 'aktif')
                    ->action(function (User $record): void {
                        MemberAccountActions::setActive($record, true);

                        Notification::make()->success()->title('Akun diaktifkan')->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus anggota ini?')
                    ->modalDescription('Data anggota, simpanan, pinjaman, dan e-wallet-nya ikut terhapus. Tindakan ini tidak dapat dibatalkan.')
                    ->action(function (User $record): void {
                        MemberAccountActions::delete($record);

                        Notification::make()->success()->title('Anggota dihapus')->send();
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
