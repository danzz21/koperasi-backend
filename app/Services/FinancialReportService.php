<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * @return array<string, mixed>
     */
    public function annual(int $year): array
    {
        $marginMurabahah = (float) DB::table('pinjamen')
            ->whereIn('status', ['aktif', 'lunas'])
            ->sum(DB::raw('angsuran * 0.10'));
        $shuMudharabah = (float) DB::table('pinjamen')
            ->where('tipe', 'mudharabah')
            ->whereIn('status', ['aktif', 'lunas'])
            ->sum(DB::raw('angsuran * 0.60'));
        $ppobIncome = (float) DB::table('ppob_transaksi')
            ->where('status', 'success')
            ->whereYear('created_at', $year)
            ->sum(DB::raw('harga - nominal'));
        $totalIncome = $marginMurabahah + $shuMudharabah + $ppobIncome;
        $operationalExpenses = 2400000;
        $netIncome = $totalIncome - $operationalExpenses;
        $totalSavings = (float) DB::table('simpanans')->where('status', 'aktif')->sum('nominal');
        $receivables = (float) DB::table('pinjamen')->where('status', 'aktif')->sum('sisa_pinjaman');
        $cash = max($totalSavings - $receivables, 0) + $netIncome;
        $totalAssets = $cash + $receivables;
        $equity = $totalAssets - $totalSavings;

        return [
            'tahun' => $year,
            'laba_rugi' => [
                'margin_murabahah' => $marginMurabahah,
                'shu_mudharabah' => $shuMudharabah,
                'pendapatan_ppob' => $ppobIncome,
                'total_pendapatan' => $totalIncome,
                'beban_operasional' => $operationalExpenses,
                'laba_bersih' => $netIncome,
            ],
            'neraca' => [
                'kas' => $cash,
                'piutang' => $receivables,
                'total_aset' => $totalAssets,
                'kewajiban' => $totalSavings,
                'ekuitas' => $equity,
            ],
            'simpanan_per_jenis' => DB::table('simpanans')->where('status', 'aktif')
                ->select('jenis', DB::raw('SUM(nominal) as total'), DB::raw('COUNT(*) as jumlah'))
                ->groupBy('jenis')->get(),
            'pembiayaan_per_akad' => DB::table('pinjamen')
                ->select(
                    'tipe',
                    DB::raw('COUNT(*) as jumlah'),
                    DB::raw('SUM(nominal) as total_nominal'),
                    DB::raw('SUM(sisa_pinjaman) as total_sisa'),
                )
                ->groupBy('tipe')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function daily(string $date): array
    {
        $income = (float) DB::table('transaksi')->where('tipe', 'kredit')->whereDate('tanggal', $date)->sum('jumlah');
        $expenses = (float) DB::table('transaksi')->where('tipe', 'debet')->whereDate('tanggal', $date)->sum('jumlah');

        return [
            'tanggal' => $date,
            'total_masuk' => $income,
            'total_keluar' => $expenses,
            'selisih' => $income - $expenses,
            'jumlah_transaksi' => DB::table('transaksi')->whereDate('tanggal', $date)->count(),
            'rincian' => DB::table('transaksi')->whereDate('tanggal', $date)
                ->select('jenis', 'tipe', DB::raw('SUM(jumlah) as total'), DB::raw('COUNT(*) as jumlah_transaksi'))
                ->groupBy('jenis', 'tipe')->get(),
        ];
    }
}
