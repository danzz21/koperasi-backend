<?php

namespace App\Services;

use App\Models\Cicilan;
use App\Models\Simpanan;
use App\Models\User;
use App\Support\MemberNotifier;
use Illuminate\Support\Facades\DB;

class PaymentGatewayService
{
    public function history(User $user)
    {
        return DB::table('payment_gateway_transaksi')
            ->where('id_anggota', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findPayment(User $user, string $orderId)
    {
        return DB::table('payment_gateway_transaksi')
            ->where('id_anggota', $user->id)
            ->where('order_id', $orderId)
            ->first();
    }

    public function simulatePayment(User $user, string $orderId)
    {
        return DB::transaction(function () use ($user, $orderId) {
            $payment = DB::table('payment_gateway_transaksi')
                ->where('id_anggota', $user->id)
                ->where('order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status !== 'pending') {
                abort(422, 'Transaksi tidak valid atau sudah diproses.');
            }

            if ($payment->jenis_pembayaran === 'cicilan') {
                $installment = Cicilan::query()
                    ->whereKey($payment->ref_id)
                    ->where('id_anggota', $user->id)
                    ->whereIn('status', ['belumbayar', 'denda'])
                    ->first();

                if (! $installment || (float) $payment->nominal !== (float) $installment->nominal) {
                    abort(422, 'Nominal pembayaran tidak sesuai dengan tagihan cicilan.');
                }

                InstallmentSettlement::settle((int) $installment->id);
            }

            if ($payment->jenis_pembayaran === 'simpanan') {
                $saving = Simpanan::query()->whereKey($payment->ref_id)
                    ->where('id_anggota', $user->id)
                    ->where('status', 'tidak_aktif')
                    ->first();

                if (! $saving) {
                    abort(422, 'Setoran simpanan tidak tersedia untuk diproses.');
                }

                $saving->update(['status' => 'aktif']);
                MemberNotifier::send(
                    $user,
                    'Setoran simpanan berhasil',
                    'Pembayaran simpanan Anda berhasil dan saldo telah diperbarui.',
                    'simpanan.paid',
                    '/anggota/simpanan',
                );
            }

            DB::table('payment_gateway_transaksi')->where('id', $payment->id)->update([
                'status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

            return $payment;
        });
    }

    /**
     * @param  array<string, int|float|string>  $data
     */
    public function create(User $user, string $jenis, array $data): string
    {
        $adminFee = match ($data['payment_method']) {
            'bca_va', 'bni_va', 'bri_va' => 4000,
            'gopay', 'dana' => 1000,
            default => 0,
        };
        $refId = (string) ($data['id'] ?? '');

        if ($jenis === 'cicilan') {
            $installment = DB::table('cicilans')->where('id', $data['id'])
                ->where('id_anggota', $user->id)
                ->whereIn('status', ['belumbayar', 'denda'])->first();
            abort_unless($installment, 422, 'Cicilan tidak tersedia untuk dibayar.');
            abort_unless(
                (float) $data['nominal'] === (float) $installment->nominal,
                422,
                'Nominal pembayaran harus sama dengan tagihan cicilan.',
            );
        }

        if ($jenis === 'simpanan') {
            $pendingWithdrawal = DB::table('withdrawal_requests')
                ->where('id_anggota', $user->id)
                ->whereIn('status', ['pending', 'disetujui'])->exists();
            abort_unless(! $pendingWithdrawal, 422, 'Setoran tidak dapat dilakukan saat pengunduran diri sedang diproses.');

            $refId = (string) DB::table('simpanans')->insertGetId([
                'id_anggota' => $user->id,
                'jenis' => $data['jenis'],
                'nominal' => $data['nominal'],
                'bunga' => 0,
                'status' => 'tidak_aktif',
                'keterangan' => 'Menunggu pembayaran',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $orderId = strtoupper($jenis).'-'.now()->format('ymdHis').'-'.random_int(1000, 9999);
        DB::table('payment_gateway_transaksi')->insert([
            'id_anggota' => $user->id,
            'order_id' => $orderId,
            'jenis_pembayaran' => $jenis,
            'ref_id' => $refId,
            'nominal' => $data['nominal'],
            'biaya_admin' => $adminFee,
            'total_bayar' => $data['nominal'] + $adminFee,
            'payment_method' => $data['payment_method'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $orderId;
    }
}
