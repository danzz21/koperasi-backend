<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    public function riwayat(Request $request)
    {
        return response()->json(['data' => DB::table('payment_gateway_transaksi')
            ->where('id_anggota', $request->user()->id)->orderByDesc('created_at')->get()]);
    }

    public function status(Request $request, string $orderId)
    {
        $payment = DB::table('payment_gateway_transaksi')
            ->where('id_anggota', $request->user()->id)->where('order_id', $orderId)->first();
        return $payment
            ? response()->json(['data' => $payment])
            : response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
    }

    public function prosesCicilan(Request $request)
    {
        return $this->create($request, 'cicilan');
    }

    public function prosesSimpanan(Request $request)
    {
        return $this->create($request, 'simpanan');
    }

    public function simulasiBayar(Request $request)
    {
        $data = $request->validate(['order_id' => 'required|string']);
        $updated = DB::table('payment_gateway_transaksi')
            ->where('id_anggota', $request->user()->id)->where('order_id', $data['order_id'])
            ->where('status', 'pending')->update(['status' => 'paid', 'paid_at' => now(), 'updated_at' => now()]);
        $payment = DB::table('payment_gateway_transaksi')->where('order_id', $data['order_id'])->first();
        if ($updated && $payment?->jenis_pembayaran === 'cicilan') {
            DB::table('cicilans')->where('id', $payment->ref_id)->where('status', 'belumbayar')->update([
                'status' => 'terbayar',
                'tgl_bayar' => now()->toDateString(),
                'updated_at' => now(),
            ]);
        }
        if ($updated && $payment?->jenis_pembayaran === 'simpanan' && $payment->ref_id) {
            DB::table('simpanans')->where('id', $payment->ref_id)->update([
                'status' => 'aktif',
                'updated_at' => now(),
            ]);
        }
        return response()->json(['success' => (bool) $updated]);
    }

    public function getMetode()
    {
        return response()->json(['metode' => [
            ['kode' => 'transfer', 'nama' => 'Transfer Bank'],
            ['kode' => 'qris', 'nama' => 'QRIS'],
        ]]);
    }

    private function create(Request $request, string $jenis)
    {
        $data = $request->validate([
            'nominal' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);
        $refId = (string) ($request->id ?? '');
        if ($jenis === 'cicilan') {
            $request->validate(['id' => 'required|integer']);
            $exists = DB::table('cicilans')->where('id', $request->id)
                ->where('id_anggota', $request->user()->id)->where('status', 'belumbayar')->exists();
            abort_unless($exists, 422, 'Cicilan tidak tersedia untuk dibayar.');
        }
        if ($jenis === 'simpanan') {
            $request->validate(['jenis' => 'required|in:pokok,wajib,sukarela']);
            $refId = (string) DB::table('simpanans')->insertGetId([
                'id_anggota' => $request->user()->id,
                'jenis' => $request->jenis,
                'nominal' => $data['nominal'],
                'bunga' => 0,
                'status' => 'tidak_aktif',
                'keterangan' => 'Menunggu pembayaran',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $orderId = strtoupper($jenis) . '-' . now()->format('ymdHis') . '-' . random_int(1000, 9999);
        DB::table('payment_gateway_transaksi')->insert([
            'id_anggota' => $request->user()->id, 'order_id' => $orderId,
            'jenis_pembayaran' => $jenis, 'ref_id' => $refId,
            'nominal' => $data['nominal'], 'biaya_admin' => 0, 'total_bayar' => $data['nominal'],
            'payment_method' => $data['payment_method'], 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['order_id' => $orderId], 201);
    }
}
