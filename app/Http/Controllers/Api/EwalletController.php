<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientSavingsException;
use App\Http\Controllers\Controller;
use App\Models\EwalletTransaction;
use App\Support\EwalletService;
use App\Support\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    /** Metode topup manual (diverifikasi admin). */
    private const METODE_MANUAL = [
        'transfer_bca', 'transfer_bsi', 'transfer_bri', 'transfer_mandiri',
        'transfer_bni', 'transfer', 'bank_transfer', 'qr', 'tunai',
    ];

    public function __construct(
        private readonly EwalletService $ewallet,
        private readonly MidtransService $midtrans,
    ) {
    }

    /**
     * Saldo + ringkasan e-wallet anggota.
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
            ->get(['id', 'tipe', 'status', 'nominal', 'metode', 'gateway', 'keterangan', 'created_at']);

        return response()->json([
            'data' => [
                'saldo'            => $this->ewallet->saldo($id),
                'topup_pending'    => $riwayat->where('tipe', 'topup')->where('status', 'pending')->count(),
                'riwayat_singkat'  => $riwayatSingkat,
                'total_topup'      => (float) DB::table('ewallet_transactions')
                    ->where('id_anggota', $id)->whereIn('tipe', ['topup', 'pindah'])
                    ->where('status', 'sukses')->sum('nominal'),
                'total_pembayaran' => (float) DB::table('ewallet_transactions')
                    ->where('id_anggota', $id)->where('tipe', 'bayar')
                    ->where('status', 'sukses')->sum('nominal'),
                'simpanan_tersedia' => $this->simpananTersedia($id),
                'gateway_aktif'     => $this->midtrans->isConfigured(),
                'rekening'          => config('services.midtrans.bank_manual'),
            ],
        ]);
    }

    /**
     * Saldo simpanan yang bisa dipindahkan ke e-wallet.
     *
     * @return array<string, float>
     */
    private function simpananTersedia(int $idAnggota): array
    {
        $rows = DB::table('simpanans')
            ->where('id_anggota', $idAnggota)
            ->where('status', 'aktif')
            ->select('jenis', DB::raw('COALESCE(SUM(nominal),0) AS total'))
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        return [
            'pokok'    => (float) ($rows['pokok'] ?? 0),
            'sukarela' => (float) ($rows['sukarela'] ?? 0),
        ];
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
        $metodeLokal = [...self::METODE_MANUAL, 'midtrans_qris', ...array_keys(MidtransService::VA_BANKS)];

        $validated = $request->validate([
            'nominal'    => 'required|numeric|min:10000|max:10000000',
            'metode'     => ['required', 'string', Rule::in($metodeLokal)],
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nominal.min' => 'Nominal topup minimal Rp ' . number_format(EwalletService::MIN_TOPUP, 0, ',', '.'),
            'nominal.max' => 'Nominal topup maksimal Rp 10.000.000 per permintaan.',
            'metode.in'   => 'Metode topup tidak dikenal.',
        ]);

        $id      = (int) $request->user()->id;
        $nominal = (float) $validated['nominal'];
        $metode  = $validated['metode'];
        $user    = $request->user();

        $trx = $this->ewallet->buatPermintaanTopup($id, $nominal, $metode, $validated['keterangan'] ?? null);

        // ── Kanal otomatis (Midtrans) ──
        $channel = $this->midtrans->channelFor($metode);

        if ($channel !== null) {
            if (! $this->midtrans->isConfigured()) {
                $this->ewallet->gagalkanTopup($trx, 'Payment gateway belum dikonfigurasi.');

                return response()->json([
                    'message' => 'Topup QRIS/Virtual Account belum tersedia karena MIDTRANS_SERVER_KEY belum diisi. '
                        .'Sementara itu gunakan Transfer Bank manual atau pindahkan saldo dari simpanan.',
                ], 503);
            }

            $charge = $this->midtrans->createTopupCharge(
                $trx->ref_id,
                (int) $nominal,
                $channel,
                $user->nama_lengkap,
                $user->email,
            );

            if (empty($charge['token']) && empty($charge['qris_string']) && empty($charge['kode_va'])) {
                $this->ewallet->gagalkanTopup($trx, $charge['message'] ?? 'Gagal membuat pembayaran.');

                return response()->json([
                    'message' => $charge['message'] ?? 'Gagal membuat pembayaran di payment gateway.',
                ], 502);
            }

            $trx->forceFill([
                'gateway'     => 'midtrans',
                'kode_va'     => $charge['kode_va'],
                'payment_url' => $charge['redirect_url'],
                'expired_at'  => $charge['expired_at'],
                'keterangan'  => ($trx->keterangan ?: 'Topup E-Wallet via '.strtoupper($channel))
                    .($charge['qris_string'] ? ' | QRIS: '.substr($charge['qris_string'], 0, 40).'…' : ''),
            ])->save();
        } else {
            // ── Kanal manual: nomor VA unik agar mudah dicocokkan admin ──
            $trx->forceFill([
                'gateway'    => 'manual',
                'kode_va'    => $this->ewallet->kodeVaUnik(),
                'expired_at' => now()->addDay(),
            ])->save();
        }

        return response()->json([
            'message' => $channel !== null
                ? 'Selesaikan pembayaran di halaman pembayaran. Saldo otomatis bertambah setelah pembayaran berhasil.'
                : 'Permintaan topup diterima. Transfer sesuai instruksi, lalu unggah bukti agar admin memverifikasi.',
            'data'    => [
                'id'          => $trx->id,
                'nominal'     => $trx->nominal,
                'metode'      => $trx->metode,
                'status'      => $trx->status,
                'ref_id'      => $trx->ref_id,
                'gateway'     => $trx->gateway,
                'kode_va'     => $trx->kode_va,
                'payment_url' => $trx->payment_url,
                'qris_string' => $charge['qris_string'] ?? null,
                'expired_at'  => optional($trx->expired_at)->toIso8601String(),
                'bank'        => config('services.midtrans.bank_manual'),
            ],
            'saldo'   => $this->ewallet->saldo($id),
        ], 201);
    }

    /**
     * Pindahkan saldo simpanan sendiri ke E-Wallet (instan, tanpa admin).
     */
    public function pindah(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'jenis'   => 'required|string|in:sukarela,pokok',
        ]);

        $id = (int) $request->user()->id;

        try {
            $trx = $this->ewallet->pindahDariSimpanan(
                $id,
                (float) $validated['nominal'],
                $validated['jenis'],
            );
        } catch (InsufficientSavingsException $e) {
            return response()->json([
                'message'    => $e->getMessage(),
                'tersedia'   => $e->tersedia,
                'dibutuhkan' => $e->dibutuhkan,
            ], 422);
        }

        return response()->json([
            'message' => 'Saldo berhasil dipindahkan ke E-Wallet.',
            'data'    => [
                'id'      => $trx->id,
                'nominal' => $trx->nominal,
                'saldo'   => $this->ewallet->saldo($id),
            ],
        ], 201);
    }

    /**
     * Unggah bukti transfer untuk topup manual yang menunggu verifikasi.
     */
    public function bukti(Request $request): JsonResponse
    {
        $request->validate([
            'id'    => 'required|integer|exists:ewallet_transactions,id',
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $trx = EwalletTransaction::where('id', $request->id)
            ->where('id_anggota', (int) $request->user()->id)
            ->where('tipe', 'topup')
            ->firstOrFail();

        $path = $request->file('bukti')->store('bukti-topup', 'public');

        $this->ewallet->lampirkanBukti($trx, $path);

        return response()->json([
            'message' => 'Bukti transfer terkirim. Admin akan memverifikasi dalam waktu dekat.',
            'bukti'   => $trx->fresh()->bukti_transfer,
        ]);
    }

    /**
     * Riwayat lengkap mutasi e-wallet anggota.
     */
    public function riwayat(Request $request): JsonResponse
    {
        $query = EwalletTransaction::where('id_anggota', (int) $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(100);

        if ($request->filled('tipe') && in_array($request->input('tipe'), ['topup', 'bayar', 'pindah'], true)) {
            $query->where('tipe', $request->input('tipe'));
        }

        return response()->json([
            'saldo'   => $this->ewallet->saldo((int) $request->user()->id),
            'riwayat' => $query->get(),
        ]);
    }
}

