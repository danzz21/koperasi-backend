<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EwalletApiService;
use App\Services\EwalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * E-Wallet Koperasi (sisi anggota).
 *
 * - GET  /api/anggota/ewallet         → saldo + ringkasan +_mutasi singkat
 * - POST /api/anggota/ewallet/topup   → topup (manual / QRIS / Virtual Account)
 * - POST /api/anggota/ewallet/pindah  → pindahkan saldo simpanan ke e-wallet
 * - POST /api/anggota/ewallet/bukti   → unggah bukti transfer manual
 * - GET  /api/anggota/ewallet/riwayat → riwayat lengkap
 */
class EwalletController extends Controller
{
    public function __construct(private readonly EwalletApiService $ewallet) {}

    /**
     * Saldo + ringkasan e-wallet anggota.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->ewallet->overview($request->user())]);
    }

    /**
     * Ajukan topup.
     *
     * Metode manual   → permintaan pending + nomor VA unik + rekening kooperasi
     *                   (uang ditransfer manual, saldo masuk setelah admin
     *                   menyetujui di Dashboard PPOB).
     * Metode Midtrans → charge QRIS/Virtual Account dibuat sungguhan; saldo
     *                   masuk otomatis lewat webhook notifikasi.
     */
    public function topup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:10000|max:10000000',
            'metode' => ['required', 'string', Rule::in($this->ewallet->topupMethods())],
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nominal.min' => 'Nominal topup minimal Rp '.number_format(EwalletService::MIN_TOPUP, 0, ',', '.'),
            'nominal.max' => 'Nominal topup maksimal Rp 10.000.000 per permintaan.',
            'metode.in' => 'Metode topup tidak dikenal.',
        ]);

        $result = $this->ewallet->topup($request->user(), $validated);

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Pindahkan saldo simpanan sendiri ke E-Wallet (instan, tanpa admin).
     */
    public function pindah(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'jenis' => 'required|string|in:sukarela,pokok',
        ]);

        $result = $this->ewallet->transferSavings($request->user(), $validated);

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Unggah bukti transfer untuk topup manual yang menunggu verifikasi.
     */
    public function bukti(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:ewallet_transactions,id',
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        return response()->json($this->ewallet->uploadProof(
            $request->user(),
            (int) $request->id,
            $request->file('bukti'),
        ));
    }

    /**
     * Riwayat lengkap mutasi e-wallet anggota.
     */
    public function riwayat(Request $request): JsonResponse
    {
        return response()->json($this->ewallet->history($request->user(), $request->input('tipe')));
    }
}
