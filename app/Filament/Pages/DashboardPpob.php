<?php

namespace App\Filament\Pages;

use App\Models\EwalletTransaction;
use App\Models\PpobTransaksi;
use App\Support\EwalletService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class DashboardPpob extends Page
{
    protected static ?string $title = 'Dashboard PPOB & E-Wallet';
    protected static ?string $navigationLabel = 'Dashboard PPOB';
    protected static ?string $slug = 'dashboard-ppob';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bolt';
    protected static ?int $navigationSort = -1;
    protected static string | UnitEnum | null $navigationGroup = 'Menu Utama';



    public function getView(): string
    {
        return 'filament.pages.dashboard-ppob';
    }

    public function getViewData(): array
    {
        // ── 1. Stat Utama PPOB ──
        $totalTransaksi = (int) DB::table('ppob_transaksi')->count();

        $totalBerhasil  = (int) DB::table('ppob_transaksi')
            ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
            ->count();

        $totalPending   = (int) DB::table('ppob_transaksi')
            ->whereIn('status', ['pending', 'menunggu'])
            ->count();

        $totalGagal     = (int) DB::table('ppob_transaksi')
            ->whereIn('status', ['gagal', 'failed', 'ditolak', 'batal'])
            ->count();

        $totalOmzet = (float) DB::table('ppob_transaksi')
            ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
            ->sum('harga');

        // Jika harga 0 atau kosong, fallback ke nominal
        if ($totalOmzet <= 0) {
            $totalOmzet = (float) DB::table('ppob_transaksi')
                ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
                ->sum('nominal');
        }

        $omzetBulanIni = (float) DB::table('ppob_transaksi')
            ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('harga');

        if ($omzetBulanIni <= 0) {
            $omzetBulanIni = (float) DB::table('ppob_transaksi')
                ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('nominal');
        }

        // Estimasi Profit Margin Koperasi (selisih harga - nominal)
        $estimasiProfit = (float) DB::table('ppob_transaksi')
            ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
            ->selectRaw('COALESCE(SUM(CASE WHEN harga > nominal THEN harga - nominal ELSE nominal * 0.02 END), 0) as profit')
            ->value('profit');

        // Hari Ini
        $transaksiHariIni = (int) DB::table('ppob_transaksi')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $omzetHariIni = (float) DB::table('ppob_transaksi')
            ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
            ->whereDate('created_at', now()->toDateString())
            ->sum('harga');

        // ── 2. Breakdown per Kategori Produk ──
        $categories = [
            'pulsa'         => ['label' => 'Pulsa Reguler', 'icon' => 'fas fa-sim-card', 'color' => 'bg-emerald-100 text-emerald-700', 'border' => '#10b981'],
            'paket_data'    => ['label' => 'Paket Data & Internet', 'icon' => 'fas fa-wifi', 'color' => 'bg-blue-100 text-blue-700', 'border' => '#3b82f6'],
            'token_listrik' => ['label' => 'Token Listrik PLN', 'icon' => 'fas fa-bolt', 'color' => 'bg-amber-100 text-amber-700', 'border' => '#f59e0b'],
            'ewallet'       => ['label' => 'Top-Up E-Wallet', 'icon' => 'fas fa-wallet', 'color' => 'bg-purple-100 text-purple-700', 'border' => '#8b5cf6'],
        ];

        $productBreakdown = [];
        foreach ($categories as $key => $meta) {
            $totalCat = DB::table('ppob_transaksi')->where('jenis_produk', $key)->count();
            $berhasilCat = DB::table('ppob_transaksi')->where('jenis_produk', $key)->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])->count();
            $pendingCat  = DB::table('ppob_transaksi')->where('jenis_produk', $key)->whereIn('status', ['pending', 'menunggu'])->count();
            $gagalCat    = DB::table('ppob_transaksi')->where('jenis_produk', $key)->whereIn('status', ['gagal', 'failed', 'ditolak', 'batal'])->count();
            $omzetCat    = (float) DB::table('ppob_transaksi')->where('jenis_produk', $key)->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])->sum('harga');
            if ($omzetCat <= 0) {
                $omzetCat = (float) DB::table('ppob_transaksi')->where('jenis_produk', $key)->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])->sum('nominal');
            }

            $productBreakdown[$key] = [
                'label'    => $meta['label'],
                'icon'     => $meta['icon'],
                'color'    => $meta['color'],
                'border'   => $meta['border'],
                'total'    => $totalCat,
                'berhasil' => $berhasilCat,
                'pending'  => $pendingCat,
                'gagal'    => $gagalCat,
                'omzet'    => $omzetCat,
            ];
        }

        // ── 3. Breakdown Metode Pembayaran ──
        $paymentMethods = DB::table('ppob_transaksi')
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('SUM(harga) as total_omzet'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // ── 4. Chart 12 Bulan (Tren PPOB) ──
        $chartLabels = [];
        $chartTrx    = [];
        $chartOmzet  = [];

        for ($i = 11; $i >= 0; $i--) {
            $date          = now()->startOfMonth()->subMonths($i);
            $chartLabels[] = $date->translatedFormat('M');

            $cTrx = DB::table('ppob_transaksi')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $cOmzet = (float) DB::table('ppob_transaksi')
                ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('harga');

            if ($cOmzet <= 0) {
                $cOmzet = (float) DB::table('ppob_transaksi')
                    ->whereIn('status', ['sukses', 'berhasil', 'success', 'aktif'])
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('nominal');
            }

            $chartTrx[]   = $cTrx;
            $chartOmzet[] = $cOmzet;
        }

        // ── 5. 10 Transaksi Terbaru Anggota ──
        $recentTransactions = DB::table('ppob_transaksi')
            ->leftJoin('users', 'ppob_transaksi.id_anggota', '=', 'users.id')
            ->select(
                'ppob_transaksi.*',
                'users.nama_lengkap as nama_anggota',
                'users.username as username_anggota'
            )
            ->orderByDesc('ppob_transaksi.created_at')
            ->limit(10)
            ->get();

        $successRate = $totalTransaksi > 0 ? round(($totalBerhasil / $totalTransaksi) * 100, 1) : 0;

        // ── 6. E-Wallet Koperasi ──
        $ewalletStats = [
            'total_saldo'     => (float) DB::table('ewallets')->sum('saldo'),
            'jumlah_dompet'   => (int) DB::table('ewallets')->count(),
            'topup_bulan'     => (float) DB::table('ewallet_transactions')
                ->where('tipe', 'topup')->where('status', 'sukses')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('nominal'),
            'topup_hari'      => (float) DB::table('ewallet_transactions')
                ->where('tipe', 'topup')->where('status', 'sukses')
                ->whereDate('created_at', now()->toDateString())
                ->sum('nominal'),
            'pemakaian_bulan' => (float) DB::table('ewallet_transactions')
                ->where('tipe', 'bayar')->where('status', 'sukses')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('nominal'),
            'pending'         => (int) EwalletTransaction::where('tipe', 'topup')->where('status', 'pending')->count(),
            'transaksi_hari'  => (int) DB::table('ewallet_transactions')
                ->whereDate('created_at', now()->toDateString())
                ->count(),
        ];

        // Permintaan topup yang menunggu verifikasi admin
        $ewalletPending = EwalletTransaction::with('anggota:id,nama_lengkap,username')
            ->where('tipe', 'topup')
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        // Mutasi terbaru (topup masuk & pembayaran keluar)
        $ewalletRecent = EwalletTransaction::with('anggota:id,nama_lengkap,username')
            ->latest()
            ->limit(10)
            ->get();

        $ewalletTopupHariIni = (int) DB::table('ewallet_transactions')
            ->where('tipe', 'topup')->where('status', 'pending')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return [
            'totalTransaksi'    => $totalTransaksi,
            'totalBerhasil'     => $totalBerhasil,
            'totalPending'      => $totalPending,
            'totalGagal'        => $totalGagal,
            'totalOmzet'        => $totalOmzet,
            'omzetBulanIni'     => $omzetBulanIni,
            'estimasiProfit'    => $estimasiProfit,
            'transaksiHariIni'  => $transaksiHariIni,
            'omzetHariIni'      => $omzetHariIni,
            'successRate'       => $successRate,
            'productBreakdown'  => $productBreakdown,
            'paymentMethods'    => $paymentMethods,
            'chartLabels'       => json_encode($chartLabels),
            'chartTrx'          => json_encode($chartTrx),
            'chartOmzet'        => json_encode($chartOmzet),
            'recentTransactions'=> $recentTransactions,
            'ewalletStats'        => $ewalletStats,
            'ewalletPending'      => $ewalletPending,
            'ewalletRecent'       => $ewalletRecent,
            'ewalletTopupHariIni' => $ewalletTopupHariIni,
        ];
    }

    /** Setujui permintaan topup E-Wallet (dipanggil dari kartu tracking). */
    public function approveTopup(int $id): void
    {
        $trx = EwalletTransaction::find($id);

        if (! $trx || $trx->tipe !== 'topup' || $trx->status !== 'pending') {
            Notification::make()
                ->danger()
                ->title('Topup tidak ditemukan')
                ->body('Permintaan ini mungkin sudah diproses sebelumnya.')
                ->send();

            return;
        }

        app(EwalletService::class)->setujuiTopup($trx);

        Notification::make()
            ->success()
            ->title('Topup disetujui')
            ->body('Saldo ' . ($trx->anggota?->nama_lengkap ?: 'anggota')
                . ' bertambah Rp ' . number_format((float) $trx->nominal, 0, ',', '.'))
            ->send();
    }

    /** Tolak permintaan topup E-Wallet. */
    public function rejectTopup(int $id): void
    {
        $trx = EwalletTransaction::find($id);

        if (! $trx || $trx->tipe !== 'topup' || $trx->status !== 'pending') {
            Notification::make()
                ->danger()
                ->title('Topup tidak ditemukan')
                ->body('Permintaan ini mungkin sudah diproses sebelumnya.')
                ->send();

            return;
        }

        app(EwalletService::class)->tolakTopup($trx);

        Notification::make()
            ->warning()
            ->title('Topup ditolak')
            ->body('Saldo tidak berubah. Anggota dapat mengajukan topup baru.')
            ->send();
    }
}
