<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdminPpobService
{
    /**
     * @param  array{filter: string, jenis: string, search: string, bulan: string}  $filters
     * @return array<string, mixed>
     */
    public function listing(array $filters): array
    {
        $stats = [
            'total' => DB::table('ppob_transaksi')->count(),
            'berhasil' => DB::table('ppob_transaksi')->where('status', 'success')->count(),
            'pending' => DB::table('ppob_transaksi')->where('status', 'pending')->count(),
            'gagal' => DB::table('ppob_transaksi')->where('status', 'failed')->count(),
            'total_omzet' => (int) DB::table('ppob_transaksi')->where('status', 'success')->sum('harga'),
            'omzet_bulan' => (int) DB::table('ppob_transaksi')->where('status', 'success')
                ->whereRaw("DATE_FORMAT(created_at,'%Y-%m') = ?", [$filters['bulan']])
                ->sum('harga'),
        ];

        $breakdown = DB::table('ppob_transaksi')
            ->select(
                'jenis_produk',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as berhasil"),
                DB::raw("SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) as gagal"),
                DB::raw("SUM(CASE WHEN status='success' THEN harga ELSE 0 END) as omzet")
            )
            ->groupBy('jenis_produk')
            ->get();

        $query = DB::table('ppob_transaksi as pt')
            ->select('pt.*', 'a.nama_lengkap', 'a.nomor_anggota')
            ->leftJoin('anggota as a', 'a.id_anggota', '=', 'pt.id_anggota')
            ->orderByDesc('pt.created_at');

        if ($filters['filter'] !== 'semua') {
            $query->where('pt.status', $filters['filter']);
        }

        if ($filters['jenis'] !== 'semua') {
            $query->where('pt.jenis_produk', $filters['jenis']);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($subquery) use ($search) {
                $subquery->where('a.nama_lengkap', 'like', "%$search%")
                    ->orWhere('pt.nomor_tujuan', 'like', "%$search%")
                    ->orWhere('pt.ref_id', 'like', "%$search%")
                    ->orWhere('a.nomor_anggota', 'like', "%$search%");
            });
        }

        return [
            'stats' => $stats,
            'breakdown' => $breakdown,
            'transaksi' => $query->paginate(25),
            'filter' => $filters['filter'],
            'jenis' => $filters['jenis'],
            'search' => $filters['search'],
            'bulan' => $filters['bulan'],
        ];
    }

    public function find(int $id)
    {
        $transaction = DB::table('ppob_transaksi as pt')
            ->select('pt.*', 'a.nama_lengkap', 'a.nomor_anggota', 'a.no_hp')
            ->leftJoin('anggota as a', 'a.id_anggota', '=', 'pt.id_anggota')
            ->where('pt.id_transaksi', $id)
            ->first();

        if (! $transaction) {
            return null;
        }

        return [
            'transaksi' => $transaction,
            'payment_data' => DB::table('payment_gateway_transaksi')
                ->where('order_id', $transaction->payment_ref)->first(),
        ];
    }

    public function updateStatus(int $id, string $status, ?string $description): void
    {
        DB::table('ppob_transaksi')->where('id_transaksi', $id)->update([
            'status' => $status,
            'keterangan' => $description ?? '',
            'updated_at' => now(),
        ]);

        $transaction = DB::table('ppob_transaksi')->where('id_transaksi', $id)->first();
        if ($transaction && $transaction->payment_ref) {
            $paymentStatus = match ($status) {
                'success' => 'paid',
                'failed' => 'failed',
                default => 'pending',
            };
            DB::table('payment_gateway_transaksi')->where('order_id', $transaction->payment_ref)->update([
                'status' => $paymentStatus,
                'paid_at' => $status === 'success' ? now() : null,
                'updated_at' => now(),
            ]);
        }
    }

    public function exportRows(string $month)
    {
        return DB::table('ppob_transaksi as pt')
            ->select('pt.*', 'a.nomor_anggota', 'a.nama_lengkap')
            ->leftJoin('anggota as a', 'a.id_anggota', '=', 'pt.id_anggota')
            ->whereRaw("DATE_FORMAT(pt.created_at,'%Y-%m') = ?", [$month])
            ->orderByDesc('pt.created_at')
            ->get();
    }
}
