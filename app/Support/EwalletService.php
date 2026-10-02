<?php

namespace App\Support;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InsufficientSavingsException;
use App\Models\Ewallet;
use App\Models\EwalletTransaction;
use App\Models\Simpanan;
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

    /**
     * Pindahkan saldo simpanan anggota ke E-Wallet (instan, tanpa admin).
     *
     * Pemotongan memakai antrean FIFO: saldo yang paling lama masuk dipakai
     * lebih dulu supaya riwayatnya tetap masuk akal.
     *
     * @throws InsufficientSavingsException
     */
    public function pindahDariSimpanan(int $idAnggota, float $nominal, string $jenis = 'sukarela'): EwalletTransaction
    {
        return DB::transaction(function () use ($idAnggota, $nominal, $jenis) {
            $rows = Simpanan::where('id_anggota', $idAnggota)
                ->where('jenis', $jenis)
                ->where('status', 'aktif')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            $tersedia = (float) $rows->sum('nominal');

            if ($tersedia < $nominal) {
                throw new InsufficientSavingsException(
                    $tersedia,
                    $nominal,
                    'Saldo simpanan '.$jenis.' kamu hanya Rp '.number_format($tersedia, 0, ',', '.')
                        .', tidak cukup untuk dipindahkan Rp '.number_format($nominal, 0, ',', '.').'.',
                );
            }

            $sisa = $nominal;

            foreach ($rows as $row) {
                if ($sisa <= 0) {
                    break;
                }

                $potong     = min((float) $row->nominal, $sisa);
                $row->nominal = (float) $row->nominal - $potong;
                $row->save();

                $sisa -= $potong;
            }

            $wallet    = $this->wallet($idAnggota);
            $saldoBaru = (float) $wallet->saldo + $nominal;

            $wallet->saldo = $saldoBaru;
            $wallet->save();

            return EwalletTransaction::create([
                'id_anggota'    => $idAnggota,
                'tipe'          => 'pindah',
                'status'        => 'sukses',
                'nominal'       => $nominal,
                'metode'        => 'simpanan_'.$jenis,
                'gateway'       => 'internal',
                'ref_id'        => $this->refId('PINDOH'),
                'balance_after' => $saldoBaru,
                'keterangan'    => 'Pindah dari Simpanan '.ucfirst($jenis).' ke E-Wallet',
            ]);
        });
    }

    /**
     * Kreditkan topup otomatis (dipanggil webhook payment gateway).
     * Idempoten: transaksi yang sudah berstatus 'sukses' tidak diubah lagi.
     */
    public function kreditkanTopup(EwalletTransaction $trx, ?string $catatan = null): EwalletTransaction
    {
        return DB::transaction(function () use ($trx, $catatan) {
            if ($trx->status === 'sukses') {
                return $trx;
            }

            $wallet    = $this->wallet((int) $trx->id_anggota);
            $saldoBaru = (float) $wallet->saldo + (float) $trx->nominal;

            $wallet->saldo = $saldoBaru;
            $wallet->save();

            $trx->forceFill([
                'status'        => 'sukses',
                'balance_after' => $saldoBaru,
                'keterangan'    => $trx->keterangan
                    ?: 'Topup E-Wallet otomatis'.($catatan ? ' ('.$catatan.')' : ''),
            ])->save();

            return $trx;
        });
    }

    /** Tandai topup gagal (kedaluwarsa / ditolak bank). */
    public function gagalkanTopup(EwalletTransaction $trx, ?string $alasan = null): EwalletTransaction
    {
        if ($trx->status === 'sukses') {
            return $trx;
        }

        $trx->forceFill([
            'status'        => 'gagal',
            'balance_after' => (float) $this->wallet((int) $trx->id_anggota)->saldo,
            'keterangan'    => $alasan ? $trx->keterangan.' — '.$alasan : $trx->keterangan,
        ])->save();

        return $trx;
    }

    /** Simpan bukti transfer yang diunggah anggota. */
    public function lampirkanBukti(EwalletTransaction $trx, string $path): EwalletTransaction
    {
        $trx->forceFill(['bukti_transfer' => $path])->save();

        return $trx;
    }

    /** Nomor virtual account unik untuk topup manual per permintaan. */
    public function kodeVaUnik(): string
    {
        do {
            $kode = '88'.random_int(100000000, 999999999);
        } while (EwalletTransaction::where('kode_va', $kode)->exists());

        return $kode;
    }

    private function refId(string $prefix): string
    {
        return $prefix.'-'.now()->format('ymdHis').'-'.strtoupper(substr(uniqid(), -4));
    }
}
