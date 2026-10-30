<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Integrasi payment gateway Midtrans (Snap/RESTful) untuk topup E-Wallet.
 *
 * Dua alur didukung:
 * - qris  → QR dinamis (string QR / link app)
 * - va_*  → Virtual Account per bank (bca, bni, bri, mandiri, bjb, bni)
 *
 * Semua request memakai HTTP fake-able supaya bisa diuji tanpa jaringan.
 */
class MidtransService
{
    /** Bank yang tersedia untuk Virtual Account. */
    public const VA_BANKS = [
        'va_bca' => 'bca',
        'va_bni' => 'bni',
        'va_bri' => 'bri',
        'va_mandiri' => 'mandiri',
        'va_bjb' => 'bjb',
        'va_permata' => 'permata',
    ];

    /**
     * Apakah gateway siap dipakai? Butuh SERVER_KEY yang benar-benar diisi
     * (bukan placeholder bawaan .env).
     */
    public function isConfigured(): bool
    {
        $key = (string) config('services.midtrans.server_key', '');

        if (! config('services.midtrans.enabled', true)) {
            return false;
        }

        if ($key === '' || str_contains($key, 'xxxx') || str_contains($key, 'your-')) {
            return false;
        }

        return true;
    }

    public function channelFor(string $metode): ?string
    {
        if ($metode === 'midtrans_qris') {
            return 'qris';
        }

        return self::VA_BANKS[$metode] ?? null;
    }

    /**
     * Buat charge topup. Balik: token, redirect_url, kode VA, string QRIS.
     *
     * @return array<string, mixed>
     */
    public function createTopupCharge(
        string $orderId,
        int $nominal,
        string $channel,
        ?string $nama = null,
        ?string $email = null,
    ): array {
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $nominal,
            ],
            'custom_expiry_unit' => 'hour',
            'custom_expiry_duration' => 24,
        ];

        if ($nama || $email) {
            $payload['customer_details'] = array_filter([
                'first_name' => $nama ?: 'Anggota',
                'email' => $email,
            ]);
        }

        if ($channel === 'qris') {
            $payload['qris'] = ['acquirer' => 'gopay'];
        } else {
            $payload['bank_transfer'] = ['bank' => $channel];
        }

        $response = $this->post($payload);

        $body = $response->json() ?? [];

        return [
            'token' => $body['token'] ?? null,
            'redirect_url' => $body['redirect_url'] ?? null,
            'kode_va' => $this->findAction($body, 'va_number')
                ?? data_get($body, 'va_numbers.0.va_number'),
            'qris_string' => $this->findAction($body, 'qr_string'),
            'expired_at' => isset($body['expiry_time'])
                ? date('Y-m-d H:i:s', strtotime($body['expiry_time']))
                : null,
            'message' => $response->successful()
                ? null
                : (data_get($body, 'error_messages.0') ?? 'Gagal membuat pembayaran.'),
        ];
    }

    /**
     * Verifikasi signature notifikasi Midtrans:
     * sha512(order_id + status_code + gross_amount + server_key).
     */
    public function verifyNotification(array $payload): bool
    {
        $expected = hash('sha512',
            ($payload['order_id'] ?? '')
            .($payload['status_code'] ?? '')
            .($payload['gross_amount'] ?? '')
            .(string) config('services.midtrans.server_key')
        );

        return hash_equals($expected, (string) ($payload['signature_key'] ?? ''));
    }

    /** Status notifikasi yang berarti uang sudah masuk. */
    public function isPaid(array $payload): bool
    {
        return in_array($payload['transaction_status'] ?? '', ['capture', 'settlement'], true)
            && in_array($payload['fraud_status'] ?? '', ['accept', 'clear', null, ''], true);
    }

    /** Status notifikasi yang berarti transaksi gagal/expired. */
    public function isFailed(array $payload): bool
    {
        return in_array($payload['transaction_status'] ?? '', ['expire', 'deny', 'cancel', 'failure', 'partial_failure'], true);
    }

    private function post(array $payload): Response
    {
        $url = rtrim((string) config('services.midtrans.api_url'), '/').'/charge';

        return Http::withBasicAuth(
            (string) config('services.midtrans.server_key'),
            ''
        )
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($url, $payload);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|null
     */
    private function findAction(array $body, string $name): ?string
    {
        $actions = $body['actions'] ?? [];

        if (! is_array($actions)) {
            return null;
        }

        foreach ($actions as $action) {
            if (($action['name'] ?? null) === $name) {
                return $action['value'] ?? null;
            }
        }

        return null;
    }
}
