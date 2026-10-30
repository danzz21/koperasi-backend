<?php

namespace App\Filament\Resources\Pinjamen\Pages;

use App\Filament\Resources\Pinjamen\PinjamanResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListPinjamen extends ListRecords
{
    protected static string $resource = PinjamanResource::class;

    public function getView(): string { return 'filament.pages.pinjaman-list'; }
    public function getTitle(): string { return ''; }

    public function getViewData(): array
    {
        // Stat cards
        $aktif   = DB::table('pinjamen')->where('status','aktif')->count();
        $totalNominal = (float) DB::table('pinjamen')->where('status','aktif')->sum('nominal');
        $pending = DB::table('pinjamen')->where('status','pending')->count();

        // Mendekati jatuh tempo (cicilan belumbayar dalam 3 hari ke depan)
        $jatuhTempo = DB::table('cicilans')
            ->where('status','belumbayar')
            ->where('tgl_tempo','<=', now()->addDays(3)->toDateString())
            ->where('tgl_tempo','>=', now()->toDateString())
            ->count();

        // Jumlah per tipe
        $perTipe = DB::table('pinjamen')
            ->select('tipe', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tipe')
            ->get()->keyBy('tipe');

        $totalAll = DB::table('pinjamen')->count();

        // Filter
        $activeTab = request('tab', 'semua');
        $search    = request('search', '');

        $query = DB::table('pinjamen as p')
            ->join('users as u', 'u.id', '=', 'p.id_anggota')
            ->select(
                'p.id','p.tipe','p.nominal','p.sisa_pinjaman','p.tenor',
                'p.bunga_per_bulan','p.angsuran','p.status','p.created_at',
                'u.nama_lengkap','u.id as user_id'
            )
            ->orderByDesc('p.created_at');

        if ($activeTab !== 'semua') {
            $query->where('p.tipe', $activeTab);
        }
        if ($search) {
            $query->where('u.nama_lengkap', 'like', "%$search%");
        }

        $pinjamans = $query->paginate(15);

        $createUrl = PinjamanResource::getUrl('create');

        return [
            'aktif'        => $aktif,
            'totalNominal' => $totalNominal,
            'pending'      => $pending,
            'jatuhTempo'   => $jatuhTempo,
            'perTipe'      => $perTipe,
            'totalAll'     => $totalAll,
            'pinjamans'    => $pinjamans,
            'activeTab'    => $activeTab,
            'search'       => $search,
            'createUrl'    => $createUrl,
        ];
    }
}
