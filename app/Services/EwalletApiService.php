<?php

namespace App\Services;

use App\Exceptions\InsufficientSavingsException;
use App\Models\EwalletTransaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class EwalletApiService
{
    private const MANUAL_METHODS = [
        'transfer_bca', 'transfer_bsi', 'transfer_bri', 'transfer_mandiri',
        'transfer_bni', 'transfer', 'bank_transfer', 'qr', 'tunai',
    ];

    public function __construct(
        private readonly EwalletService $ewallet,
        private readonly MidtransService $midtrans,
    ) {}

    /**
     * @return list<string>
     */
    public function topupMethods(): array
    {
        return [...self::MANUAL_METHODS, 'midtrans_qris', ...array_keys(MidtransService::VA_BANKS)];
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(User $user): array
    {
        $id = (int) $user->id;
        $history = EwalletTransaction::query()->where('id_anggota', $id)
            ->orderByDesc('created_at')->limit(30)->get();
        $shortHistory = EwalletTransaction::query()->where('id_anggota', $id)
            ->orderByDesc('created_at')->limit(5)
            ->get(['id', 'tipe', 'status', 'nominal', 'metode', 'gateway', 'keterangan', 'created_at']);

        return [
            'saldo' => $this->ewallet->saldo($id),
            'topup_pending' => $history->where('tipe', 'topup')->where('status', 'pending')->count(),
            'riwayat_singkat' => $shortHistory,
            'total_topup' => (float) DB::table('ewallet_transactions')->where('id_anggota', $id)
                ->whereIn('tipe', ['topup', 'pindah'])->where('status', 'sukses')->sum('nominal'),
            'total_pembayaran' => (float) DB::table('ewallet_transactions')->where('id_anggota', $id)
                ->where('tipe', 'bayar')->where('status', 'sukses')->sum('nominal'),
            'simpanan_tersedia' => $this->availableSavings($id),
            'gateway_aktif' => $this->midtrans->isConfigured(),
            'auto_approve' => (bool) config('services.topup.auto_approve_manual', false),
            'rekening' => config('services.midtrans.bank_manual'),
        ];
    }

    /**
     * @param  array{nominal: int|float|string, metode: string, keterangan?: string|null}  $data
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function topup(User $user, array $data): array
    {
        $id = (int) $user->id;
        $nominal = (float) $data['nominal'];
        $method = $data['metode'];
        $transaction = $this->ewallet->buatPermintaanTopup(
            $id,
            $nominal,
            $method,
            $data['keterangan'] ?? null,
        );
        $channel = $this->midtrans->channelFor($method);
        $charge = [];

        if ($channel !== null) {
            if (! $this->midtrans->isConfigured()) {
                $this->ewallet->gagalkanTopup($transaction, 'Payment gateway belum dikonfigurasi.');

                return [
                    'status' => 503,
                    'payload' => [
                        'message' => 'Topup QRIS/Virtual Account belum tersedia karena MIDTRANS_SERVER_KEY belum diisi. '
                            .'Sementara itu gunakan Transfer Bank manual atau pindahkan saldo dari simpanan.',
                    ],
                ];
            }

            $charge = $this->midtrans->createTopupCharge(
                $transaction->ref_id,
                (int) $nominal,
                $channel,
                $user->nama_lengkap,
                $user->email,
            );

            if (empty($charge['token']) && empty($charge['qris_string']) && empty($charge['kode_va'])) {
                $this->ewallet->gagalkanTopup($transaction, $charge['message'] ?? 'Gagal membuat pembayaran.');

                return [
                    'status' => 502,
                    'payload' => ['message' => $charge['message'] ?? 'Gagal membuat pembayaran di payment gateway.'],
                ];
            }

            $qrisString = $charge['qris_string'] ?? null;
            $transaction->forceFill([
                'gateway' => 'midtrans',
                'kode_va' => $charge['kode_va'] ?? null,
                'payment_url' => $charge['redirect_url'] ?? null,
                'expired_at' => $charge['expired_at'] ?? null,
                'keterangan' => ($transaction->keterangan ?: 'Topup E-Wallet via '.strtoupper($channel))
                    .($qrisString ? ' | QRIS: '.substr($qrisString, 0, 40).'…' : ''),
            ])->save();
        } else {
            $transaction->forceFill([
                'gateway' => 'manual',
                'kode_va' => $this->ewallet->kodeVaUnik(),
                'expired_at' => now()->addDay(),
            ])->save();
        }

        return [
            'status' => 201,
            'payload' => [
                'message' => $channel !== null
                    ? 'Selesaikan pembayaran di halaman pembayaran. Saldo otomatis bertambah setelah pembayaran berhasil.'
                    : 'Permintaan topup diterima. Transfer sesuai instruksi, lalu unggah bukti agar admin memverifikasi.',
                'data' => [
                    'id' => $transaction->id,
                    'nominal' => $transaction->nominal,
                    'metode' => $transaction->metode,
                    'status' => $transaction->status,
                    'ref_id' => $transaction->ref_id,
                    'gateway' => $transaction->gateway,
                    'kode_va' => $transaction->kode_va,
                    'payment_url' => $transaction->payment_url,
                    'qris_string' => $charge['qris_string'] ?? null,
                    'expired_at' => optional($transaction->expired_at)->toIso8601String(),
                    'bank' => config('services.midtrans.bank_manual'),
                ],
                'saldo' => $this->ewallet->saldo($id),
            ],
        ];
    }

    /**
     * @param  array{nominal: int|float|string, jenis: string}  $data
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function transferSavings(User $user, array $data): array
    {
        try {
            $transaction = $this->ewallet->pindahDariSimpanan(
                (int) $user->id,
                (float) $data['nominal'],
                $data['jenis'],
            );
        } catch (InsufficientSavingsException $exception) {
            return [
                'status' => 422,
                'payload' => [
                    'message' => $exception->getMessage(),
                    'tersedia' => $exception->tersedia,
                    'dibutuhkan' => $exception->dibutuhkan,
                ],
            ];
        }

        return [
            'status' => 201,
            'payload' => [
                'message' => 'Saldo berhasil dipindahkan ke E-Wallet.',
                'data' => [
                    'id' => $transaction->id,
                    'nominal' => $transaction->nominal,
                    'saldo' => $this->ewallet->saldo((int) $user->id),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function uploadProof(User $user, int $transactionId, UploadedFile $proof): array
    {
        $transaction = EwalletTransaction::query()->where('id', $transactionId)
            ->where('id_anggota', (int) $user->id)
            ->where('tipe', 'topup')
            ->firstOrFail();
        $path = $proof->store('bukti-topup', 'public');
        $this->ewallet->lampirkanBukti($transaction, $path);

        if (config('services.topup.auto_approve_manual', false) && $transaction->fresh()->status === 'pending') {
            $transaction = $this->ewallet->kreditkanTopup($transaction, 'Bukti transfer terverifikasi otomatis');

            return [
                'message' => 'Bukti diterima. Saldo E-Wallet langsung ditambahkan.',
                'bukti' => $transaction->bukti_transfer,
                'status' => $transaction->status,
                'saldo' => $this->ewallet->saldo((int) $user->id),
            ];
        }

        $transaction = $transaction->fresh();

        return [
            'message' => 'Bukti transfer terkirim. Admin akan memverifikasi dalam waktu dekat.',
            'bukti' => $transaction->bukti_transfer,
            'status' => $transaction->status,
        ];
    }

    /**
     * @return array{saldo: float, riwayat: mixed}
     */
    public function history(User $user, ?string $type): array
    {
        $query = EwalletTransaction::query()->where('id_anggota', (int) $user->id)
            ->orderByDesc('created_at')->limit(100);

        if (in_array($type, ['topup', 'bayar', 'pindah'], true)) {
            $query->where('tipe', $type);
        }

        return [
            'saldo' => $this->ewallet->saldo((int) $user->id),
            'riwayat' => $query->get(),
        ];
    }

    /**
     * @return array<string, float>
     */
    private function availableSavings(int $memberId): array
    {
        $rows = DB::table('simpanans')->where('id_anggota', $memberId)->where('status', 'aktif')
            ->select('jenis', DB::raw('COALESCE(SUM(nominal),0) AS total'))
            ->groupBy('jenis')->pluck('total', 'jenis');

        return [
            'pokok' => (float) ($rows['pokok'] ?? 0),
            'sukarela' => (float) ($rows['sukarela'] ?? 0),
        ];
    }
}
