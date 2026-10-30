<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SimpananService;
use Illuminate\Http\Request;

class SimpananController extends Controller
{
    public function __construct(private readonly SimpananService $simpanan) {}

    public function index(Request $request)
    {
        return response()->json($this->simpanan->forMember($request->user()));
    }

    public function setTenor(Request $request)
    {
        $request->validate(['tenor' => 'required|integer|in:12,24,36,48,60']);

        return response()->json([
            'message' => 'Tenor berhasil disimpan.',
            'tenor' => $this->simpanan->setTenor((int) $request->tenor),
        ]);
    }

    public function storePokok(Request $request)
    {
        return $this->store($request, 'pokok', 10000);
    }

    public function storeWajib(Request $request)
    {
        return $this->store($request, 'wajib', 10000);
    }

    public function storeSukarela(Request $request)
    {
        return $this->store($request, 'sukarela', 5000);
    }

    private function store(Request $request, string $jenis, int $minimum)
    {
        $data = $request->validate(['jumlah' => "required|numeric|min:$minimum", 'keterangan' => 'nullable|string']);
        if (! $this->simpanan->store($request->user(), $jenis, $data)) {
            return response()->json([
                'message' => 'Setoran tidak dapat dilakukan saat pengunduran diri sedang diproses.',
            ], 422);
        }

        return response()->json(['message' => 'Setoran berhasil dikirim, menunggu verifikasi admin.'], 201);
    }
}
