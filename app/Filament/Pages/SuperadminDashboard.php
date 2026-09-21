<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class SuperadminDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Dashboard Superadmin';

    protected static ?string $title = 'Dashboard Superadmin';

    protected static ?int $navigationSort = -10;

    protected static string|UnitEnum|null $navigationGroup = 'Superadmin';

    protected string $view = 'filament.pages.superadmin-dashboard';

    public function getSubheading(): ?string
    {
        return 'Pengawasan menyeluruh: pengguna sistem, keuangan, dan jejak aktivitas (audit trail)';
    }

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'superadmin'
            && auth()->user()?->role === 'superadmin';
    }

    public function getViewData(): array
    {
        // --- User per role ---
        $totalAdmin      = DB::table('users')->where('role', 'admin')->count();
        $totalSuperadmin = DB::table('users')->where('role', 'superadmin')->count();
        $totalAnggota    = DB::table('users')->where('role', 'anggota')->count();
        $totalUser       = DB::table('users')->count();

        // --- Keuangan menyeluruh (audit finansial) ---
        $totalSimpanan = (float) DB::table('simpanans')->where('status', 'aktif')->sum('nominal');
        $totalPinjaman = (float) DB::table('pinjamen')->where('status', 'aktif')->sum('sisa_pinjaman');
        $totalTransaksi = (float) DB::table('transaksi')->sum('jumlah');
        $totalPpob       = (float) DB::table('ppob_transaksi')->sum('harga');

        // --- Aktivitas sistem ---
        $totalAuditHariIni = AuditLog::query()->whereDate('created_at', now()->toDateString())->count();
        $totalAudit        = AuditLog::query()->count();

        $aktivitasTerbaru = AuditLog::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        $topActions = AuditLog::query()
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- Chart 1: tren aktivitas audit 12 bulan terakhir ---
        $chartLabels    = [];
        $chartAktivitas = [];
        $chartTransaksi = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);

            $chartLabels[]    = $date->translatedFormat('M');
            $chartAktivitas[] = AuditLog::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $chartTransaksi[] = (int) DB::table('transaksi')
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->count();
        }

        // --- Chart 2: komposisi pengguna per role ---
        $userComposition = [$totalSuperadmin, $totalAdmin, $totalAnggota];

        return [
            'totalAdmin'         => $totalAdmin,
            'totalSuperadmin'    => $totalSuperadmin,
            'totalAnggota'       => $totalAnggota,
            'totalUser'          => $totalUser,
            'totalSimpanan'      => $totalSimpanan,
            'totalPinjaman'      => $totalPinjaman,
            'totalTransaksi'     => $totalTransaksi,
            'totalPpob'          => $totalPpob,
            'totalAuditHariIni'  => $totalAuditHariIni,
            'totalAudit'         => $totalAudit,
            'aktivitasTerbaru'   => $aktivitasTerbaru,
            'topActions'         => $topActions,
            'chartLabels'        => json_encode($chartLabels),
            'chartAktivitas'     => json_encode($chartAktivitas),
            'chartTransaksi'     => json_encode($chartTransaksi),
            'userComposition'    => json_encode($userComposition),
        ];
    }
}
