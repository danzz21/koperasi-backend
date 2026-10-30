<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminPpobService;
use Illuminate\Http\Request;

class AdminPpobController extends Controller
{
    public function __construct(private readonly AdminPpobService $ppob) {}

    /** GET /api/admin/ppob */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'semua');
        $jenis = $request->get('jenis', 'semua');
        $search = $request->get('search', '');
        $bulan = $request->get('bulan', now()->format('Y-m'));

        return response()->json($this->ppob->listing([
            'filter' => $filter,
            'jenis' => $jenis,
            'search' => $search,
            'bulan' => $bulan,
        ]));
    }

    /** GET /api/admin/ppob/{id} */
    public function show(int $id)
    {
        $result = $this->ppob->find($id);
        if (! $result) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        return response()->json($result);
    }

    /** POST /api/admin/ppob/update-status */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required|integer',
            'status' => 'required|in:success,failed,pending',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $this->ppob->updateStatus(
            (int) $request->id_transaksi,
            $request->status,
            $request->keterangan,
        );

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    }

    /** GET /api/admin/ppob/export/csv */
    public function exportCsv(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));

        $rows = $this->ppob->exportRows($bulan);

        $filename = 'ppob_'.$bulan.'_'.now()->format('His').'.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=$filename"];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'No. Anggota', 'Nama', 'Jenis', 'Provider', 'No. Tujuan', 'Produk', 'Nominal', 'Harga', 'Status', 'Metode', 'Ref', 'Tanggal']);
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
