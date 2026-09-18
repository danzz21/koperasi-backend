<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPpobController extends Controller
{
    /** GET /api/admin/ppob */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'semua');
        $jenis  = $request->get('jenis',  'semua');
        $search = $request->get('search', '');
        $bulan  = $request->get('bulan',  now()->format('Y-m'));

        // Statistik
        $stats = [
            'total'       => DB::table('ppob_transaksi')->count(),
            'berhasil'    => DB::table('ppob_transaksi')->where('status','success')->count(),
            'pending'     => DB::table('ppob_transaksi')->where('status','pending')->count(),
            'gagal'       => DB::table('ppob_transaksi')->where('status','failed')->count(),
            'total_omzet' => (int) DB::table('ppob_transaksi')->where('status','success')->sum('harga'),
            'omzet_bulan' => (int) DB::table('ppob_transaksi')
                ->where('status','success')
                ->whereRaw("DATE_FORMAT(created_at,'%Y-%m') = ?", [$bulan])
                ->sum('harga'),
        ];

        // Breakdown per jenis
        $breakdown = DB::table('ppob_transaksi')
            ->select(
                'jenis_produk',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as berhasil"),
                DB::raw("SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status='failed'  THEN 1 ELSE 0 END) as gagal"),
                DB::raw("SUM(CASE WHEN status='success' THEN harga ELSE 0 END) as omzet")
            )
            ->groupBy('jenis_produk')
            ->get();

        // Query transaksi dengan filter
        $query = DB::table('ppob_transaksi as pt')
            ->select('pt.*', 'a.nama_lengkap', 'a.nomor_anggota')
            ->leftJoin('anggota as a', 'a.id_anggota', '=', 'pt.id_anggota')
            ->orderByDesc('pt.created_at');

        if ($filter !== 'semua') $query->where('pt.status', $filter);
        if ($jenis  !== 'semua') $query->where('pt.jenis_produk', $jenis);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('a.nama_lengkap', 'like', "%$search%")
                  ->orWhere('pt.nomor_tujuan', 'like', "%$search%")
                  ->orWhere('pt.ref_id', 'like', "%$search%")
                  ->orWhere('a.nomor_anggota', 'like', "%$search%");
            });
        }

        $transaksi = $query->paginate(25);

        return response()->json([
            'stats'     => $stats,
            'breakdown' => $breakdown,
            'transaksi' => $transaksi,
            'filter'    => $filter,
            'jenis'     => $jenis,
            'search'    => $search,
            'bulan'     => $bulan,
        ]);
    }

    /** GET /api/admin/ppob/{id} */
    public function show(int $id)
    {
        $transaksi = DB::table('ppob_transaksi as pt')
            ->select('pt.*', 'a.nama_lengkap', 'a.nomor_anggota', 'a.no_hp')
            ->leftJoin('anggota as a', 'a.id_anggota', '=', 'pt.id_anggota')
            ->where('pt.id_transaksi', $id)
            ->first();

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $paymentData = DB::table('payment_gateway_transaksi')
            ->where('order_id', $transaksi->payment_ref)->first();

        return response()->json(['transaksi' => $transaksi, 'payment_data' => $paymentData]);
    }

    /** POST /api/admin/ppob/update-status */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required|integer',
            'status'       => 'required|in:success,failed,pending',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        DB::table('ppob_transaksi')->where('id_transaksi', $request->id_transaksi)->update([
            'status'     => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'updated_at' => now(),
        ]);

        // Sync ke payment_gateway_transaksi
        $trx = DB::table('ppob_transaksi')->where('id_transaksi', $request->id_transaksi)->first();
        if ($trx && $trx->payment_ref) {
            $pgStatus = match($request->status) { 'success'=>'paid','failed'=>'failed', default=>'pending' };
            DB::table('payment_gateway_transaksi')->where('order_id', $trx->payment_ref)->update([
                'status'  => $pgStatus,
                'paid_at' => $request->status === 'success' ? now() : null,
                'updated_at'=> now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    }

    /** GET /api/admin/ppob/export/csv */
    public function exportCsv(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));

        $rows = DB::table('ppob_transaksi as pt')
            ->select('pt.*', 'a.nomor_anggota', 'a.nama_lengkap')
            ->leftJoin('anggota as a', 'a.id_anggota','=','pt.id_anggota')
            ->whereRaw("DATE_FORMAT(pt.created_at,'%Y-%m') = ?", [$bulan])
            ->orderByDesc('pt.created_at')->get();

        $filename = 'ppob_' . $bulan . '_' . now()->format('His') . '.csv';
        $headers  = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=$filename"];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','No. Anggota','Nama','Jenis','Provider','No. Tujuan','Produk','Nominal','Harga','Status','Metode','Ref','Tanggal']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id_transaksi, $r->nomor_anggota, $r->nama_lengkap,
                    $r->jenis_produk, $r->provider, $r->nomor_tujuan,
                    $r->nama_produk, $r->nominal, $r->harga, $r->status,
                    $r->payment_method, $r->ref_id, $r->created_at,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
