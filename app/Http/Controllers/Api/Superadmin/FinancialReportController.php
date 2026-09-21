<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /**
     * GET /api/superadmin/financial-report?year=2026
     * Laporan laba rugi & neraca ringkas (khusus superadmin).
     */
    public function index(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);

        // ── Pendapatan ─────────────────────────────────────────────────────
        $marginMurabahah = (float) DB::table('pinjamen')
            ->whereIn('status', ['aktif', 'lunas'])
            ->sum(DB::raw('angsuran * 0.10'));

        $shuMudharabah = (float) DB::table('pinjamen')
            ->where('tipe', 'mudharabah')
            ->whereIn('status', ['aktif', 'lunas'])
            ->sum(DB::raw('angsuran * 0.60'));

        $pendapatanPpob = (float) DB::table('ppob_transaksi')
            ->where('status', 'success')
            ->whereYear('created_at', $year)
            ->sum(DB::raw('harga - nominal'));

        $totalPendapatan = $marginMurabahah + $shuMudharabah + $pendapatanPpob;

        // ── Beban ──────────────────────────────────────────────────────────
        $bebanOperasional = 2400000;
        $labaBersih       = $totalPendapatan - $bebanOperasional;

        // ── Neraca ─────────────────────────────────────────────────────────
        $totalSimpanan = (float) DB::table('simpanans')->where('status', 'aktif')->sum('nominal');
        $piutang       = (float) DB::table('pinjamen')->where('status', 'aktif')->sum('sisa_pinjaman');
        $kas           = max($totalSimpanan - $piutang, 0) + $labaBersih;
        $totalAset     = $kas + $piutang;
        $ekuitas       = $totalAset - $totalSimpanan;

        // ── Simpanan per jenis ─────────────────────────────────────────────
        $simpananPerJenis = DB::table('simpanans')
            ->where('status', 'aktif')
            ->select('jenis', DB::raw('SUM(nominal) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis')
            ->get();

        // ── Pembiayaan per akad ────────────────────────────────────────────
        $pembiayaanPerAkad = DB::table('pinjamen')
            ->select(
                'tipe',
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('SUM(nominal) as total_nominal'),
                DB::raw('SUM(sisa_pinjaman) as total_sisa'),
            )
            ->groupBy('tipe')
            ->get();

        return response()->json([
            'data' => [
                'tahun'              => $year,
                'laba_rugi'          => [
                    'margin_murabahah'  => $marginMurabahah,
                    'shu_mudharabah'    => $shuMudharabah,
                    'pendapatan_ppob'   => $pendapatanPpob,
                    'total_pendapatan'  => $totalPendapatan,
                    'beban_operasional' => $bebanOperasional,
                    'laba_bersih'       => $labaBersih,
                ],
                'neraca'             => [
                    'kas'               => $kas,
                    'piutang'           => $piutang,
                    'total_aset'        => $totalAset,
                    'kewajiban'         => $totalSimpanan,
                    'ekuitas'           => $ekuitas,
                ],
                'simpanan_per_jenis' => $simpananPerJenis,
                'pembiayaan_per_akad' => $pembiayaanPerAkad,
            ],
        ]);
    }

    /**
     * GET /api/superadmin/financial-report/daily?tanggal=2026-09-18
     */
    public function dailyReport(Request $request)
    {
        $tanggal = $request->tanggal ?? now()->toDateString();

        $masuk = (float) DB::table('transaksi')
            ->where('tipe', 'kredit')
            ->whereDate('tanggal', $tanggal)
            ->sum('jumlah');

        $keluar = (float) DB::table('transaksi')
            ->where('tipe', 'debet')
            ->whereDate('tanggal', $tanggal)
            ->sum('jumlah');

        $rincian = DB::table('transaksi')
            ->whereDate('tanggal', $tanggal)
            ->select('jenis', 'tipe', DB::raw('SUM(jumlah) as total'), DB::raw('COUNT(*) as jumlah_transaksi'))
            ->groupBy('jenis', 'tipe')
            ->get();

        return response()->json([
            'data' => [
                'tanggal'          => $tanggal,
                'total_masuk'      => $masuk,
                'total_keluar'     => $keluar,
                'selisih'          => $masuk - $keluar,
                'jumlah_transaksi' => DB::table('transaksi')->whereDate('tanggal', $tanggal)->count(),
                'rincian'          => $rincian,
            ],
        ]);
    }
}