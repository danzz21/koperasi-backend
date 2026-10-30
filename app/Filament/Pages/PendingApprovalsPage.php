<?php

namespace App\Filament\Pages;

use App\Services\PendingApprovalActions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Dasar untuk semua halaman "Persetujuan Pending" admin.
 *
 * Kartu di dashboard admin menautkan ke halaman-halaman ini, dan jumlah barisnya
 * selalu sama dengan angka pada kartu (keduanya memakai App\Support\PendingApprovals).
 */
abstract class PendingApprovalsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.pending-approvals';

    /** Diakses dari kartu dashboard, bukan dari menu sidebar. */
    protected static bool $shouldRegisterNavigation = false;

    /** Jenis pending: salah satu konstanta PendingApprovalActions. */
    abstract public static function pendingType(): string;

    /** Query data pending (memakai App\Support\PendingApprovals). */
    abstract public static function pendingQuery(): Builder;

    /** @return array<int, \Filament\Tables\Columns\Column> */
    abstract protected function columns(): array;

    protected function approveLabel(): string
    {
        return 'Setujui';
    }

    protected function rejectLabel(): string
    {
        return 'Tolak';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Daftar pengajuan yang menunggu persetujuan admin.';
    }

    public function table(Table $table): Table
    {
        $type         = static::pendingType();
        $approveLabel = $this->approveLabel();
        $rejectLabel  = $this->rejectLabel();

        return $table
            ->query(static::pendingQuery())
            ->columns($this->columns())
            ->recordActions([
                Action::make('approve')
                    ->label($approveLabel)
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading($approveLabel.' pengajuan ini?')
                    ->modalDescription('Data akan diproses dan tidak muncul lagi di daftar pending.')
                    ->modalSubmitActionLabel('Ya, '.mb_strtolower($approveLabel))
                    ->action(function (Model $record) use ($type, $approveLabel): void {
                        $ok = PendingApprovalActions::approve($type, $record);

                        $notification = Notification::make()
                            ->title($ok ? $approveLabel.' berhasil' : $approveLabel.' gagal');

                        $ok ? $notification->success() : $notification->danger();

                        $notification->send();
                    }),

                Action::make('reject')
                    ->label($rejectLabel)
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading($rejectLabel.' pengajuan ini?')
                    ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, '.mb_strtolower($rejectLabel))
                    ->action(function (Model $record) use ($type, $rejectLabel): void {
                        $ok = PendingApprovalActions::reject($type, $record);

                        $notification = Notification::make()
                            ->title($ok ? $rejectLabel.' berhasil' : $rejectLabel.' gagal');

                        $notification->danger()->send();
                    }),
            ])
            ->defaultSort('created_at', 'asc')
            ->paginated([10, 25, 50])
            ->striped()
            ->emptyStateIcon('heroicon-o-check-badge')
            ->emptyStateHeading('Tidak ada data pending')
            ->emptyStateDescription('Semua pengajuan pada kategori ini sudah diproses.');
    }
}
