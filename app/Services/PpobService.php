<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PpobService
{
    public const EWALLET = 'ewallet';

    public function __construct(private readonly EwalletService $ewallet) {}

    /**
     * @return array{riwayat: mixed, total_bulan: int}
     */
    public function overview(User $user): array
    {
        $history = DB::table('ppob_transaksi')->where('id_anggota', $user->id)
            ->orderByDesc('created_at')->limit(10)->get();
        $monthlyTotal = DB::table('ppob_transaksi')->where('id_anggota', $user->id)
            ->where('status', 'success')
            ->whereRaw("DATE_FORMAT(created_at,'%Y-%m') = ?", [now()->format('Y-m')])
            ->sum('harga');

        return ['riwayat' => $history, 'total_bulan' => (int) $monthlyTotal];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function products(string $type): array
    {
        return match ($type) {
            'pulsa' => $this->pulsaCatalog(),
            'paket_data' => $this->dataCatalog(),
            'token_listrik' => $this->electricityCatalog(),
            'ewallet' => $this->ewalletCatalog(),
        };
    }

    /**
     * @param  array{kode_produk: string, jenis_produk: string, nomor_tujuan: string, payment_method: string}  $data
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function createOrder(User $user, array $data): array
    {
        $product = collect($this->products($data['jenis_produk']))->firstWhere('kode', $data['kode_produk']);
        if (! $product) {
            return ['status' => 404, 'payload' => ['message' => 'Produk tidak ditemukan.']];
        }

        $memberId = (int) $user->id;
        $referenceId = 'PPOB-'.now()->format('ymdHis').'-'.strtoupper(substr(uniqid(), -4));
        $orderId = 'PPOB-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -6));
        $adminFee = str_contains($data['payment_method'], '_va') ? 4000 : 0;
        $total = $product['harga'] + $adminFee;
        $paymentCode = $data['payment_method'] === self::EWALLET
            ? 'EWALLET-'.strtoupper(substr($referenceId, -6))
            : $this->generatePaymentCode($data['payment_method']);
        $expiresAt = now()->addHour();
        $payWithWallet = $data['payment_method'] === self::EWALLET;
        $transactionStatus = $payWithWallet ? 'success' : 'pending';
        $paymentStatus = $payWithWallet ? 'paid' : 'pending';

        try {
            DB::transaction(function () use (
                $data,
                $memberId,
                $product,
                $total,
                $payWithWallet,
                $referenceId,
                $orderId,
                $paymentCode,
                $adminFee,
                $expiresAt,
                $transactionStatus,
                $paymentStatus,
            ) {
                if ($payWithWallet) {
                    $this->ewallet->bayar($memberId, $total, 'PPOB: '.$product['nama'], $referenceId);
                }

                DB::table('ppob_transaksi')->insert([
                    'id_anggota' => $memberId,
                    'jenis_produk' => $data['jenis_produk'],
                    'provider' => $product['provider'],
                    'nomor_tujuan' => $data['nomor_tujuan'],
                    'nominal' => $product['nominal'],
                    'harga' => $product['harga'],
                    'kode_produk' => $product['kode'],
                    'nama_produk' => $product['nama'],
                    'status' => $transactionStatus,
                    'ref_id' => $referenceId,
                    'payment_ref' => $orderId,
                    'payment_method' => $data['payment_method'],
                    'keterangan' => $payWithWallet ? 'Dibayar langsung via saldo E-Wallet' : 'Menunggu pembayaran',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $transactionId = DB::getPdo()->lastInsertId();
                DB::table('payment_gateway_transaksi')->insert([
                    'id_anggota' => $memberId,
                    'order_id' => $orderId,
                    'jenis_pembayaran' => 'ppob',
                    'ref_id' => (string) $transactionId,
                    'nominal' => $product['harga'],
                    'biaya_admin' => $adminFee,
                    'total_bayar' => $total,
                    'payment_method' => $data['payment_method'],
                    'payment_code' => $paymentCode,
                    'payment_expired' => $expiresAt,
                    'paid_at' => $payWithWallet ? now() : null,
                    'status' => $paymentStatus,
                    'keterangan' => 'PPOB: '.$product['nama'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (InsufficientBalanceException $exception) {
            return [
                'status' => 422,
                'payload' => [
                    'message' => 'Saldo E-Wallet tidak cukup. Topup saldo dulu sebelum membeli produk ini.',
                    'saldo' => $exception->saldo,
                    'dibutuhkan' => $exception->dibutuhkan,
                    'kurang' => $exception->dibutuhkan - $exception->saldo,
                ],
            ];
        }

        return [
            'status' => 201,
            'payload' => [
                'message' => $payWithWallet ? 'Pembayaran berhasil dari saldo E-Wallet!' : 'Order berhasil dibuat!',
                'order_id' => $orderId,
                'paid' => $payWithWallet,
                'payment' => [
                    'code' => $paymentCode,
                    'method' => $data['payment_method'],
                    'total' => $total,
                    'expired' => $expiresAt->format('d/m/Y H:i'),
                ],
            ],
        ];
    }

    /**
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function status(User $user, string $orderId): array
    {
        $payment = DB::table('payment_gateway_transaksi')
            ->where('order_id', $orderId)->where('id_anggota', $user->id)->first();

        if (! $payment) {
            return ['status' => 404, 'payload' => ['message' => 'Transaksi tidak ditemukan.']];
        }

        return [
            'status' => 200,
            'payload' => [
                'payment' => $payment,
                'transaksi' => DB::table('ppob_transaksi')->where('id_transaksi', $payment->ref_id)->first(),
            ],
        ];
    }

    /**
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function simulatePayment(User $user, string $orderId): array
    {
        $payment = DB::table('payment_gateway_transaksi')
            ->where('order_id', $orderId)->where('id_anggota', $user->id)->first();

        if (! $payment || $payment->status !== 'pending') {
            return ['status' => 422, 'payload' => ['success' => false, 'message' => 'Transaksi tidak valid.']];
        }

        DB::table('payment_gateway_transaksi')->where('order_id', $orderId)->update([
            'status' => 'paid',
            'paid_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('ppob_transaksi')->where('id_transaksi', $payment->ref_id)->update([
            'status' => 'success',
            'keterangan' => 'Pembayaran berhasil via simulasi',
            'updated_at' => now(),
        ]);

        $transaction = DB::table('ppob_transaksi')->find($payment->ref_id);
        $tokenCode = '';
        if ($transaction && $transaction->jenis_produk === 'token_listrik') {
            $tokenCode = implode('-', str_split(rand(10000000, 99999999).rand(10000000, 99999999), 4));
        }

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => 'Pembayaran berhasil!',
                'token_kode' => $tokenCode,
            ],
        ];
    }

    public function history(User $user)
    {
        return DB::table('ppob_transaksi')->where('id_anggota', $user->id)
            ->orderByDesc('created_at')->limit(50)->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pulsaCatalog(): array
    {
        return [
            ['kode' => 'TSEL5', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel Rp 5.000', 'nominal' => 5000, 'harga' => 6000],
            ['kode' => 'TSEL10', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel Rp 10.000', 'nominal' => 10000, 'harga' => 11000],
            ['kode' => 'TSEL20', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel Rp 20.000', 'nominal' => 20000, 'harga' => 21500],
            ['kode' => 'TSEL50', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel Rp 50.000', 'nominal' => 50000, 'harga' => 52000],
            ['kode' => 'TSEL100', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel Rp 100.000', 'nominal' => 100000, 'harga' => 103000],
            ['kode' => 'XL5', 'provider' => 'XL', 'nama' => 'XL Rp 5.000', 'nominal' => 5000, 'harga' => 5500],
            ['kode' => 'XL10', 'provider' => 'XL', 'nama' => 'XL Rp 10.000', 'nominal' => 10000, 'harga' => 10500],
            ['kode' => 'XL50', 'provider' => 'XL', 'nama' => 'XL Rp 50.000', 'nominal' => 50000, 'harga' => 51500],
            ['kode' => 'ISAT10', 'provider' => 'Indosat', 'nama' => 'Indosat Rp 10.000', 'nominal' => 10000, 'harga' => 10500],
            ['kode' => 'ISAT50', 'provider' => 'Indosat', 'nama' => 'Indosat Rp 50.000', 'nominal' => 50000, 'harga' => 51000],
            ['kode' => 'TRI10', 'provider' => 'Tri', 'nama' => 'Tri Rp 10.000', 'nominal' => 10000, 'harga' => 10200],
            ['kode' => 'TRI50', 'provider' => 'Tri', 'nama' => 'Tri Rp 50.000', 'nominal' => 50000, 'harga' => 50700],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dataCatalog(): array
    {
        return [
            ['kode' => 'TSEL_1GB_7H', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel 1 GB/7H', 'nominal' => 1, 'harga' => 12000, 'deskripsi' => '1 GB • 7 Hari'],
            ['kode' => 'TSEL_3GB_30H', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel 3 GB/30H', 'nominal' => 3, 'harga' => 35000, 'deskripsi' => '3 GB • 30 Hari'],
            ['kode' => 'TSEL_5GB_30H', 'provider' => 'Telkomsel', 'nama' => 'Telkomsel 5 GB/30H', 'nominal' => 5, 'harga' => 55000, 'deskripsi' => '5 GB • 30 Hari'],
            ['kode' => 'XL_5GB_30H', 'provider' => 'XL', 'nama' => 'XL 5 GB/30H', 'nominal' => 5, 'harga' => 50000, 'deskripsi' => '5 GB • 30 Hari'],
            ['kode' => 'ISAT_3GB_30H', 'provider' => 'Indosat', 'nama' => 'Indosat 3 GB/30H', 'nominal' => 3, 'harga' => 30000, 'deskripsi' => '3 GB • 30 Hari'],
            ['kode' => 'TRI_4GB_30H', 'provider' => 'Tri', 'nama' => 'Tri 4 GB/30H', 'nominal' => 4, 'harga' => 40000, 'deskripsi' => '4 GB • 30 Hari'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function electricityCatalog(): array
    {
        return [
            ['kode' => 'PLN20K', 'provider' => 'PLN', 'nama' => 'Token PLN Rp 20.000', 'nominal' => 20000, 'harga' => 21500, 'kwh' => '~12 kWh'],
            ['kode' => 'PLN50K', 'provider' => 'PLN', 'nama' => 'Token PLN Rp 50.000', 'nominal' => 50000, 'harga' => 51500, 'kwh' => '~32 kWh'],
            ['kode' => 'PLN100K', 'provider' => 'PLN', 'nama' => 'Token PLN Rp 100.000', 'nominal' => 100000, 'harga' => 101500, 'kwh' => '~65 kWh'],
            ['kode' => 'PLN200K', 'provider' => 'PLN', 'nama' => 'Token PLN Rp 200.000', 'nominal' => 200000, 'harga' => 201500, 'kwh' => '~130 kWh'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ewalletCatalog(): array
    {
        return [
            ['kode' => 'GOPAY50', 'provider' => 'GoPay', 'nama' => 'GoPay Rp 50.000', 'nominal' => 50000, 'harga' => 51000],
            ['kode' => 'GOPAY100', 'provider' => 'GoPay', 'nama' => 'GoPay Rp 100.000', 'nominal' => 100000, 'harga' => 101500],
            ['kode' => 'OVO50', 'provider' => 'OVO', 'nama' => 'OVO Rp 50.000', 'nominal' => 50000, 'harga' => 51000],
            ['kode' => 'OVO100', 'provider' => 'OVO', 'nama' => 'OVO Rp 100.000', 'nominal' => 100000, 'harga' => 101500],
            ['kode' => 'DANA50', 'provider' => 'DANA', 'nama' => 'DANA Rp 50.000', 'nominal' => 50000, 'harga' => 51000],
            ['kode' => 'DANA100', 'provider' => 'DANA', 'nama' => 'DANA Rp 100.000', 'nominal' => 100000, 'harga' => 101500],
            ['kode' => 'SHOPEEPAY50', 'provider' => 'ShopeePay', 'nama' => 'ShopeePay Rp 50.000', 'nominal' => 50000, 'harga' => 51000],
        ];
    }

    private function generatePaymentCode(string $method): string
    {
        if (str_contains($method, '_va')) {
            $prefix = ['bca_va' => '10200', 'bni_va' => '98890', 'mandiri_va' => '70012', 'bri_va' => '88801'];

            return ($prefix[$method] ?? '99900').rand(1000000, 9999999);
        }

        if ($method === 'qris') {
            return 'QRIS-'.strtoupper(substr(md5(uniqid()), 0, 16));
        }

        return strtoupper($method).'-'.rand(100000, 999999);
    }
}
