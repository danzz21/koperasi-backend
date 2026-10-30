<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals) {}

    public function index(Request $request)
    {
        return response()->json([
            'data' => $this->withdrawals->history($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $withdrawal = $this->withdrawals->submit($request->user());

        return response()->json([
            'message' => 'Permohonan pengunduran diri berhasil dikirim dan menunggu verifikasi pengurus.',
            'data' => $withdrawal,
        ], 201);
    }

    public function summary(Request $request)
    {
        return response()->json($this->withdrawals->summary($request->user()));
    }
}
