<?php

namespace App\Filament\Pages;

use App\Models\WithdrawalRequest;
use App\Services\WithdrawalRequestActions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class PendingWithdrawalRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.pending-withdrawal-requests';

    protected static ?string $navigationLabel = 'Pengunduran Diri';

    protected static ?string $title = 'Permohonan Pengunduran Diri';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Koperasi';

    public function table(Table $table): Table
    {
        return $table
            ->query(WithdrawalRequest::query()->with('anggota'))
            ->columns([
                TextColumn::make('anggota.nama_lengkap')->label('Anggota')->searchable()->sortable(),
                TextColumn::make('jenis_bank')->label('Bank'),
                TextColumn::make('nomor_rekening')->label('Rekening')->copyable(),
                TextColumn::make('total_simpanan')->label('Total Simpanan')->money('IDR', locale: 'id'),
                TextColumn::make('biaya_admin')->label('Biaya Admin')->money('IDR', locale: 'id'),
                TextColumn::make('jumlah_pencairan')->label('Pencairan')->money('IDR', locale: 'id'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Tanggal Ajukan')->dateTime('d M Y H:i')->sortable(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === 'pending')
                    ->action(fn (WithdrawalRequest $record) => $this->notifyAction(
                        WithdrawalRequestActions::approve($record),
                        'Permohonan disetujui',
                    )),
                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === 'pending')
                    ->action(fn (WithdrawalRequest $record) => $this->notifyAction(
                        WithdrawalRequestActions::reject($record),
                        'Permohonan ditolak',
                    )),
                Action::make('markPaid')
                    ->label('Konfirmasi Transfer')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalDescription('Gunakan setelah transfer ke rekening anggota benar-benar dilakukan.')
                    ->visible(fn (WithdrawalRequest $record): bool => $record->status === 'disetujui')
                    ->action(fn (WithdrawalRequest $record) => $this->notifyAction(
                        WithdrawalRequestActions::markPaid($record),
                        'Transfer dikonfirmasi',
                    )),
            ])
            ->defaultSort('created_at', 'asc')
            ->paginated([10, 25, 50]);
    }

    private function notifyAction(bool $success, string $title): void
    {
        $notification = Notification::make()->title($title);
        $success ? $notification->success() : $notification->danger();
        $notification->send();
    }
}
