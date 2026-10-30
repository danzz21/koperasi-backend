<?php

namespace App\Filament\Resources\Simpanans\Pages;

use App\Filament\Resources\Simpanans\SimpananResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListSimpanans extends ListRecords
{
    protected static string $resource = SimpananResource::class;

    public function getView(): string
    {
        return 'filament.pages.simpanan-list';
    }

    public function getTitle(): string { return ''; }

    public function getViewData(): array
    {
        // Stat cards
        $statsRaw = DB::table('simpanans')
            ->where('status', 'aktif')
            ->select('jenis', DB::raw('SUM(nominal) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis')
            ->get()
            ->keyBy('jenis');

        $pokok    = $statsRaw->get('pokok')    ?? (object)['total' => 0, 'jumlah' => 0];
        $wajib    = $statsRaw->get('wajib')    ?? (object)['total' => 0, 'jumlah' => 0];
        $sukarela = $statsRaw->get('sukarela') ?? (object)['total' => 0, 'jumlah' => 0];

        // Lunas pokok (status aktif, nominal >= target)
        $lunasPokok = DB::table('simpanans')
            ->where('status', 'aktif')->where('jenis', 'pokok')
            ->where('nominal', '>=', 500000)
            ->count();

        $totalSimpanan = (float)$pokok->total + (float)$wajib->total + (float)$sukarela->total;

        // Tab filter & search dari request
        $activeTab = request('tab', 'pokok');
        $search    = request('search', '');
        $anggotaId = request('anggota_id', '');

        // Query simpanan
        $query = DB::table('simpanans as s')
            ->join('users as u', 'u.id', '=', 's.id_anggota')
            ->select(
                's.id', 's.jenis', 's.nominal', 's.status', 's.keterangan',
                's.created_at', 's.updated_at',
                'u.nama_lengkap', 'u.nomor_ktp', 'u.id as user_id'
            )
            ->orderByDesc('s.updated_at');

        if ($activeTab !== 'semua') {
            $query->where('s.jenis', $activeTab);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.nama_lengkap', 'like', "%$search%")
                  ->orWhere('u.nomor_ktp', 'like', "%$search%");
            });
        }
        if ($anggotaId) {
            $query->where('s.id_anggota', $anggotaId);
        }

        $simpanans = $query->paginate(15);

        // Dropdown anggota
        $anggotaList = DB::table('users')
            ->where('role', 'anggota')->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->select('id', 'nama_lengkap')
            ->get();

        $createUrl = SimpananResource::getUrl('create');

        return [
            'pokok'        => $pokok,
            'wajib'        => $wajib,
            'sukarela'     => $sukarela,
            'lunasPokok'   => $lunasPokok,
            'totalSimpanan'=> $totalSimpanan,
            'simpanans'    => $simpanans,
            'activeTab'    => $activeTab,
            'search'       => $search,
            'anggotaId'    => $anggotaId,
            'anggotaList'  => $anggotaList,
            'createUrl'    => $createUrl,
        ];
    }
}
