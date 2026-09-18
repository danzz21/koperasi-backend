<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimpananController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $rows = DB::table('simpanans')->where('id_anggota', $user->id)
            ->orderByDesc('created_at')->get();
        $active = $rows->where('status', 'aktif');
        $summary = $active->groupBy('jenis')->map(fn ($items) => (float) $items->sum('nominal'));
        $data = [
            'sim_pokok' => $summary->get('pokok', 0),
            'sim_wajib' => $summary->get('wajib', 0),
            'sim_sukarela' => $summary->get('sukarela', 0),
            'target_wajib' => 100000,
            'bunga_sukarela' => 0,
            'tgl_sim_pokok' => optional($rows->firstWhere('jenis', 'pokok'))->created_at,
            'items' => $rows,
        ];
        return response()->json(['anggota' => $user, 'tenor' => null, 'show_tenor_modal' => false,
            'data' => $data, 'simpanan' => $rows, 'summary' => [
            'total_pokok' => $summary->get('pokok', 0), 'total_wajib' => $summary->get('wajib', 0),
            'total_sukarela' => $summary->get('sukarela', 0), 'total' => (float) $active->sum('nominal'),
        ]]);
    }

    public function setTenor(Request $request)
    {
        $request->validate(['tenor' => 'required|integer|in:12,24,36,48,60']);
        return response()->json(['message' => 'Tenor berhasil disimpan.', 'tenor' => (int) $request->tenor]);
    }

    public function storePokok(Request $request) { return $this->store($request, 'pokok', 10000); }
    public function storeWajib(Request $request) { return $this->store($request, 'wajib', 10000); }
    public function storeSukarela(Request $request) { return $this->store($request, 'sukarela', 5000); }

    private function store(Request $request, string $jenis, int $minimum)
    {
        $data = $request->validate(['jumlah' => "required|numeric|min:$minimum", 'keterangan' => 'nullable|string']);
        DB::table('simpanans')->insert([
            'id_anggota' => $request->user()->id, 'jenis' => $jenis, 'nominal' => $data['jumlah'],
            'keterangan' => $data['keterangan'] ?? null, 'status' => 'tidak_aktif',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Setoran berhasil dikirim, menunggu verifikasi admin.'], 201);
    }
}
