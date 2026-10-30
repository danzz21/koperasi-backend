<?php

namespace App\Filament\Resources\Cicilans\Pages;

use App\Filament\Resources\Cicilans\CicilanResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListCicilans extends ListRecords
{
    protected static string $resource = CicilanResource::class;

    public function getView(): string { return 'filament.pages.angsuran-list'; }
    public function getTitle(): string { return ''; }

    public function getViewData(): array
    {
        $activeTab = request('tab', 'qard');
        $search    = request('search', '');

        // Hitung jumlah per tipe dari pinjamen
        $perTipe = DB::table('pinjamen')
            ->select('tipe', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tipe')
            ->get()->keyBy('tipe');

        // Query utama: ambil data per pinjaman + progress cicilan
        $query = DB::table('pinjamen as p')
            ->join('users as u', 'u.id', '=', 'p.id_anggota')
            ->leftJoin(DB::raw('(SELECT id_pinjaman, COUNT(*) as total_cicilan,
                SUM(CASE WHEN status = "terbayar" THEN 1 ELSE 0 END) as lunas,
                SUM(CASE WHEN status = "terbayar" THEN nominal ELSE 0 END) as terbayar_nominal,
                SUM(CASE WHEN status != "terbayar" THEN nominal ELSE 0 END) as sisa_nominal
                FROM cicilans GROUP BY id_pinjaman) as c'), 'c.id_pinjaman', '=', 'p.id')
            ->select(
                'p.id', 'p.tipe', 'p.nominal', 'p.tenor', 'p.angsuran',
                'p.status', 'p.created_at as tgl_akad',
                'p.cicilan_ke',
                'u.nama_lengkap', 'u.username', 'u.nomor_ktp',
                DB::raw('COALESCE(c.total_cicilan, 0) as total_cicilan'),
                DB::raw('COALESCE(c.lunas, 0) as cicilan_lunas'),
                DB::raw('COALESCE(c.terbayar_nominal, 0) as terbayar_nominal'),
                DB::raw('COALESCE(c.sisa_nominal, p.nominal) as sisa_nominal')
            )
            ->where('p.tipe', $activeTab)
            ->where('p.status', '!=', 'ditolak')
            ->orderByDesc('p.created_at');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.nama_lengkap', 'like', "%$search%")
                  ->orWhere('u.nomor_anggota', 'like', "%$search%");
            });
        }

        $angsurans = $query->paginate(15);

        $editBaseUrl = CicilanResource::getUrl('index');

        return [
            'angsurans'  => $angsurans,
            'perTipe'    => $perTipe,
            'activeTab'  => $activeTab,
            'search'     => $search,
            'editBaseUrl'=> $editBaseUrl,
        ];
    }
}
