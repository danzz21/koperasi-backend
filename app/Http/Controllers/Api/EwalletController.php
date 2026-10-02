<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EwalletTransaction;
use App\Support\EwalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * E-Wallet Koperasi (sisi anggota).
 *
 * - GET  /api/anggota/ewallet         → saldo + riwayat mutasi singkat
 * - POST /api/anggota/ewallet/topup   → minta topup (menunggu verifikasi admin)
 * - GET  /api/anggota/ewallet/riwayat → riwayat lengkap
 */
class EwalletController extends Controller
{
    public function __construct(private readonly EwalletService $ewallet)
    {
    }

    /**
     * Saldo + ringkasan e-wallet anggota.
     * Response: { data: { saldo, topup_pending, riwayat_singkat[], total_topup, total_pembayaran } }
     */
    public function index(Request $request): JsonResponse
    {
        $id = (int) $request->user()->id;

        $riwayat = EwalletTransaction::where('id_anggota', $id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $riwayatSingkat = EwalletTransaction::where('id_anggota', $id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'tipe', 'status', 'nominal', 'metode', 'keterangan', 'created_at']);

        return response()->json([
            'data' => [
                'saldo'            => $this->ewallet->saldo($id),
                'topup_pending'    => $riwayat->where('tipe', 'topup')->where('status', 'pending')->count(),
                'riwayat_singkat'  => $riwayatSingkat,
                'total_topup'      => (float) DB::table('ewallet_transactions')
                    ->where('id_anggota', $id)->where('tipe', 'topup')->where('status', 'sukses')->sum('nominal'),
                'total_pembayaran' => (float) DB::table('ewallet_transactions')
                    ->where('id_anggota', $id)->where('tipe', 'bayar')->where('status', 'sukses')->sum('nominal'),
            ],
        ]);
    }

    /**
     * Ajukan permintaan topup e-wallet.
     * Body: { nominal, metode, keterangan? }
     */
    public function topup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nominal'    => 'required|numeric|min:10000|max:10000000',
            'metode'     => 'required|string|in:transfer_bca,transfer_bsi,transfer_bri,transfer_mandiri,transfer_bni,qr,tunai,bank_transfer,transfer',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nominal.min'  => 'Nominal topup minimal Rp ' . number_format(EwalletService::MIN_TOPUP, 0, ',', '.'),
            'nominal.max'  => 'Nominal topup maksimal Rp 10.000.000 per permintaan.',
            'metode.in'    => 'Metode topup tidak dikenal. Pilih salah satu: Transfer BCA, BRI, atau BSI.',
        ]);

        $trx = $this->ewallet->buatPermintaanTopup(
            (int) $request->user()->id,
            (float) $validated['nominal'],
            $validated['metode'],
            $validated['keterangan'] ?? null,
        );

        return response()->json([
            'message' => 'Permintaan topup diterima. Saldo akan bertambah setelah diverifikasi admin.',
            'data'    => [
                'id'      => $trx->id,
                'nominal' => $trx->nominal,
                'metode'  => $trx->metode,
                'status'  => $trx->status,
                'ref_id'  => $trx->ref_id,
            ],
        ], 201);
    }

    /**
     * Riwayat lengkap mutasi e-wallet anggota.
     * Response: { data: [] }
     */
    public function riwayat(Request $request): JsonResponse
    {
        $data = EwalletTransaction::where('id_anggota', (int) $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get(['id', 'tipe', 'status', 'nominal', 'metode', 'keterangan', 'balance_after', 'created_at', 'ref_id']);

        return response()->json(['data' => $data]);
    }
}
