<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CicilanService;
use Illuminate\Http\Request;

class CicilanController extends Controller
{
    public function __construct(private readonly CicilanService $cicilan) {}

    public function index(Request $request)
    {
        return response()->json($this->cicilan->forMember($request->user()));
    }

    public function bayar(Request $request)
    {
        $data = $request->validate([
            'id_pinjaman' => 'required|integer', 'nominal' => 'required|numeric|min:1',
            'metode' => 'nullable|in:transfer,tunai,e-wallet',
        ]);
        $result = $this->cicilan->pay($request->user(), $data);

        return response()->json(['message' => $result['message']], $result['status']);
    }

    public function riwayat(Request $request)
    {
        return response()->json(['riwayat' => $this->cicilan->history($request->user())]);
    }

    public function setTenor(Request $request)
    {
        $request->validate(['tenor' => 'required|integer|min:1|max:60']);

        return response()->json(['message' => 'Tenor berhasil disimpan.', 'tenor' => (int) $request->tenor]);
    }
}
