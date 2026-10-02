<?php

namespace App\Support;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Ewallet;
use App\Models\EwalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Layanan E-Wallet Koperasi.
 *
 * Prinsip:
 * - Saldo selalu diubah di dalam transaksi database dengan row lock,
 *   supaya tidak ada saldo yang jadi negatif akibat aksi bersamaan.
 * - Setiap mutasi selalu meninggalkan jejak di `ewallet_transactions`,
 *   termasuk `balance_after` untuk tracking di dashboard admin.
 */
class EwalletService
{
    public const MIN_TOPUP = 10000;

    /** Ambil (atau buat bila belum ada) saldo milik satu anggota. */
    public function saldo(int $idAnggota): float
    {
        return (float) DB::transaction(function () use ($idAnggota) {
            return $this->wallet($idAnggota)->saldo;
        });
    }

    /**
     * Permintaan topup → status 'pending' sampai disetujui admin.
     *
     * @return EwalletTransaction
     */
    public function buatPermintaanTopup(int $idAnggota, float $nominal, string $metode, ?string $keterangan = null): EwalletTransaction
    {
        return EwalletTransaction::create([
            'id_anggota' => $idAnggota,
            'tipe'       => 'topup',
            'status'     => 'pending',
            'nominal'    => $nominal,
            'metode'     => $metode,
            'ref_id'     => 'TOPUP-'.now()->format('ymdHis').'-'.strtoupper(substr(uniqid(), -4)),
            'keterangan' => $keterangan ?: 'Topup E-Wallet via '.ucfirst(str_replace('_', ' ', $metode)),
        ]);
    }

    /** Setujui topup → saldo bertambah. */
    public function setujuiTopup(EwalletTransaction $trx): EwalletTransaction
    {
        return DB::transaction(function () use ($trx) {
            $wallet = $this->wallet((int) $trx->id_anggota);

            $saldoBaru = (float) $wallet->saldo + (float) $trx->nominal;

            $wallet->saldo = $saldoBaru;
            $wallet->save();

            $trx->forceFill([
                'status'       => 'sukses',
                'balance_after' => $saldoBaru,
            ])->save();

            return $trx;
        });
    }

    /** Tolak topup → transaksi gagal, saldo tidak berubah. */
    public function tolakTopup(EwalletTransaction $trx): EwalletTransaction
    {
        return DB::transaction(function () use ($trx) {
            $trx->forceFill([
                'status'        => 'gagal',
                'balance_after' => (float) $this->wallet((int) $trx->id_anggota)->saldo,
            ])->save();

            return $trx;
        });
    }

    /**
     * Bayar pakai saldo E-Wallet (dipotong langsung).
     *
     * @throws InsufficientBalanceException bila saldo kurang
     */
    public function bayar(
        int $idAnggota,
        float $nominal,
        string $keterangan,
        ?string $refId = null,
    ): EwalletTransaction {
        return DB::transaction(function () use ($idAnggota, $nominal, $keterangan, $refId) {
            $wallet = $this->wallet($idAnggota);
            $saldo   = (float) $wallet->saldo;

            if ($saldo < $nominal) {
                throw new InsufficientBalanceException($saldo, $nominal);
            }

            $saldoBaru = $saldo - $nominal;

            $wallet->saldo = $saldoBaru;
            $wallet->save();

            return EwalletTransaction::create([
                'id_anggota'    => $idAnggota,
                'tipe'          => 'bayar',
                'status'        => 'sukses',
                'nominal'       => $nominal,
                'metode'        => 'saldo_ewallet',
                'ref_id'        => $refId,
                'balance_after' => $saldoBaru,
                'keterangan'    => $keterangan,
            ]);
        });
    }

    /**
     * @param  int $idAnggota
     * @return Ewallet
     */
    protected function wallet(int $idAnggota): Ewallet
    {
        // Lock baris agar saldo bersaing tidak terbaca bersamaan
        $existing = Ewallet::where('id_anggota', $idAnggota)->lockForUpdate()->first();

        if ($existing) {
            return $existing;
        }

        return Ewallet::firstOrCreate(
            ['id_anggota' => $idAnggota],
            ['saldo'      => 0],
        );
    }
}
