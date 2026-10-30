<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\MemberAccountActions;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                // NO (row number)
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->width('50px')
                    ->alignCenter(),

                // NAMA ANGGOTA
                TextColumn::make('nama_lengkap')
                    ->label('Nama Anggota')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): string => $record->username ?? ''),

                // NO. KTP
                TextColumn::make('nomor_ktp')
                    ->label('No. KTP')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('No. KTP disalin'),

                // STATUS badge
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif'    => 'success',
                        'pending'  => 'warning',
                        'nonaktif' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->alignCenter(),

                // TGL DAFTAR
                TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->date('d M Y')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari nama, No. KTP, No. Anggota...')
            ->filters([
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
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->color('info')
                    ->tooltip('Edit'),

                DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->tooltip('Hapus')
                    ->modalHeading('Hapus anggota ini?')
                    ->modalDescription('Data anggota, simpanan, pinjaman, dan e-wallet-nya ikut terhapus. Tindakan ini tidak dapat dibatalkan.')
                    ->action(function (User $record): void {
                        MemberAccountActions::delete($record);
                        Notification::make()->success()->title('Anggota dihapus')->send();
                    }),

                ViewAction::make()
                    ->label('')
                    ->icon('heroicon-m-eye')
                    ->color('success')
                    ->tooltip('Detail'),

                Action::make('resetPassword')
                    ->label('')
                    ->icon('heroicon-m-key')
                    ->color('warning')
                    ->tooltip('Reset Password')
                    ->requiresConfirmation()
                    ->modalHeading('Reset password anggota?')
                    ->modalDescription('Password diubah ke "'.MemberAccountActions::DEFAULT_PASSWORD.'" dan anggota harus login ulang.')
                    ->modalSubmitActionLabel('Ya, reset')
                    ->action(function (User $record): void {
                        MemberAccountActions::resetPassword($record);
                        Notification::make()->warning()->title('Password direset')
                            ->body('Password '.$record->nama_lengkap.' sekarang "'.MemberAccountActions::DEFAULT_PASSWORD.'".')
                            ->send();
                    }),

                Action::make('toggleStatus')
                    ->label('')
                    ->icon(fn (User $record): string => $record->status === 'aktif'
                        ? 'heroicon-m-no-symbol'
                        : 'heroicon-m-check-circle')
                    ->color(fn (User $record): string => $record->status === 'aktif' ? 'danger' : 'success')
                    ->tooltip(fn (User $record): string => $record->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => $record->status === 'aktif'
                        ? 'Nonaktifkan anggota ini?'
                        : 'Aktifkan anggota ini?')
                    ->action(function (User $record): void {
                        $active = $record->status !== 'aktif';
                        MemberAccountActions::setActive($record, $active);
                        Notification::make()
                            ->when($active, fn ($n) => $n->success()->title('Akun diaktifkan'))
                            ->when(!$active, fn ($n) => $n->danger()->title('Akun dinonaktifkan'))
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped(false)
            ->paginated([10, 25, 50]);
    }
}
