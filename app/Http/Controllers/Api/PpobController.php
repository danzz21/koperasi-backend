<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpobController extends Controller
{
    private function idAnggota(Request $r): int
    {
        return (int) $r->user()->id;
    }

    private function katalogPulsa(): array
    {
        return [
            ['kode'=>'TSEL5',   'provider'=>'Telkomsel','nama'=>'Telkomsel Rp 5.000',   'nominal'=>5000,   'harga'=>6000],
            ['kode'=>'TSEL10',  'provider'=>'Telkomsel','nama'=>'Telkomsel Rp 10.000',  'nominal'=>10000,  'harga'=>11000],
            ['kode'=>'TSEL20',  'provider'=>'Telkomsel','nama'=>'Telkomsel Rp 20.000',  'nominal'=>20000,  'harga'=>21500],
            ['kode'=>'TSEL50',  'provider'=>'Telkomsel','nama'=>'Telkomsel Rp 50.000',  'nominal'=>50000,  'harga'=>52000],
            ['kode'=>'TSEL100', 'provider'=>'Telkomsel','nama'=>'Telkomsel Rp 100.000', 'nominal'=>100000, 'harga'=>103000],
            ['kode'=>'XL5',     'provider'=>'XL',       'nama'=>'XL Rp 5.000',          'nominal'=>5000,   'harga'=>5500],
            ['kode'=>'XL10',    'provider'=>'XL',       'nama'=>'XL Rp 10.000',         'nominal'=>10000,  'harga'=>10500],
            ['kode'=>'XL50',    'provider'=>'XL',       'nama'=>'XL Rp 50.000',         'nominal'=>50000,  'harga'=>51500],
            ['kode'=>'ISAT10',  'provider'=>'Indosat',  'nama'=>'Indosat Rp 10.000',    'nominal'=>10000,  'harga'=>10500],
            ['kode'=>'ISAT50',  'provider'=>'Indosat',  'nama'=>'Indosat Rp 50.000',    'nominal'=>50000,  'harga'=>51000],
            ['kode'=>'TRI10',   'provider'=>'Tri',      'nama'=>'Tri Rp 10.000',        'nominal'=>10000,  'harga'=>10200],
            ['kode'=>'TRI50',   'provider'=>'Tri',      'nama'=>'Tri Rp 50.000',        'nominal'=>50000,  'harga'=>50700],
        ];
    }

    private function katalogData(): array
    {
        return [
            ['kode'=>'TSEL_1GB_7H',  'provider'=>'Telkomsel','nama'=>'Telkomsel 1 GB/7H', 'nominal'=>1, 'harga'=>12000, 'deskripsi'=>'1 GB • 7 Hari'],
            ['kode'=>'TSEL_3GB_30H', 'provider'=>'Telkomsel','nama'=>'Telkomsel 3 GB/30H','nominal'=>3, 'harga'=>35000, 'deskripsi'=>'3 GB • 30 Hari'],
            ['kode'=>'TSEL_5GB_30H', 'provider'=>'Telkomsel','nama'=>'Telkomsel 5 GB/30H','nominal'=>5, 'harga'=>55000, 'deskripsi'=>'5 GB • 30 Hari'],
            ['kode'=>'XL_5GB_30H',   'provider'=>'XL',       'nama'=>'XL 5 GB/30H',       'nominal'=>5, 'harga'=>50000, 'deskripsi'=>'5 GB • 30 Hari'],
            ['kode'=>'ISAT_3GB_30H', 'provider'=>'Indosat',  'nama'=>'Indosat 3 GB/30H',  'nominal'=>3, 'harga'=>30000, 'deskripsi'=>'3 GB • 30 Hari'],
            ['kode'=>'TRI_4GB_30H',  'provider'=>'Tri',      'nama'=>'Tri 4 GB/30H',      'nominal'=>4, 'harga'=>40000, 'deskripsi'=>'4 GB • 30 Hari'],
        ];
    }

    private function katalogListrik(): array
    {
        return [
            ['kode'=>'PLN20K',  'provider'=>'PLN','nama'=>'Token PLN Rp 20.000',  'nominal'=>20000,  'harga'=>21500, 'kwh'=>'~12 kWh'],
            ['kode'=>'PLN50K',  'provider'=>'PLN','nama'=>'Token PLN Rp 50.000',  'nominal'=>50000,  'harga'=>51500, 'kwh'=>'~32 kWh'],
            ['kode'=>'PLN100K', 'provider'=>'PLN','nama'=>'Token PLN Rp 100.000', 'nominal'=>100000, 'harga'=>101500,'kwh'=>'~65 kWh'],
            ['kode'=>'PLN200K', 'provider'=>'PLN','nama'=>'Token PLN Rp 200.000', 'nominal'=>200000, 'harga'=>201500,'kwh'=>'~130 kWh'],
        ];
    }

    private function katalogEwallet(): array
    {
        return [
            ['kode'=>'GOPAY50',    'provider'=>'GoPay',    'nama'=>'GoPay Rp 50.000',    'nominal'=>50000,  'harga'=>51000],
            ['kode'=>'GOPAY100',   'provider'=>'GoPay',    'nama'=>'GoPay Rp 100.000',   'nominal'=>100000, 'harga'=>101500],
            ['kode'=>'OVO50',      'provider'=>'OVO',      'nama'=>'OVO Rp 50.000',      'nominal'=>50000,  'harga'=>51000],
            ['kode'=>'OVO100',     'provider'=>'OVO',      'nama'=>'OVO Rp 100.000',     'nominal'=>100000, 'harga'=>101500],
            ['kode'=>'DANA50',     'provider'=>'DANA',     'nama'=>'DANA Rp 50.000',     'nominal'=>50000,  'harga'=>51000],
            ['kode'=>'DANA100',    'provider'=>'DANA',     'nama'=>'DANA Rp 100.000',    'nominal'=>100000, 'harga'=>101500],
            ['kode'=>'SHOPEEPAY50','provider'=>'ShopeePay','nama'=>'ShopeePay Rp 50.000','nominal'=>50000,  'harga'=>51000],
        ];
    }

    // ── Endpoints ─────────────────────────────────────────────────────────

    /** GET /api/anggota/ppob */
    public function index(Request $request)
    {
        $id      = $this->idAnggota($request);
        $riwayat = DB::table('ppob_transaksi')->where('id_anggota', $id)->orderByDesc('created_at')->limit(10)->get();
        $totalBulan = DB::table('ppob_transaksi')
            ->where('id_anggota', $id)->where('status','success')
            ->whereRaw("DATE_FORMAT(created_at,'%Y-%m') = ?", [now()->format('Y-m')])
            ->sum('harga');

        return response()->json(['riwayat' => $riwayat, 'total_bulan' => (int)$totalBulan]);
    }

    /** GET /api/anggota/ppob/produk/pulsa */
    public function produkPulsa() { return response()->json($this->katalogPulsa()); }
    public function produkData()  { return response()->json($this->katalogData()); }
    public function produkListrik(){ return response()->json($this->katalogListrik()); }
    public function produkEwallet(){ return response()->json($this->katalogEwallet()); }

    /** POST /api/anggota/ppob/order */
    public function order(Request $request)
    {
        $request->validate([
            'kode_produk'  => 'required|string',
            'jenis_produk' => 'required|in:pulsa,paket_data,token_listrik,ewallet',
            'nomor_tujuan' => 'required|string|min:8|max:20',
            'payment_method'=> 'required|string',
        ]);

        $id = $this->idAnggota($request);
        $katalog = match($request->jenis_produk) {
            'pulsa'         => $this->katalogPulsa(),
            'paket_data'    => $this->katalogData(),
            'token_listrik' => $this->katalogListrik(),
            'ewallet'       => $this->katalogEwallet(),
        };

        $produk = collect($katalog)->firstWhere('kode', $request->kode_produk);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        $refId   = 'PPOB-' . now()->format('ymdHis') . '-' . strtoupper(substr(uniqid(), -4));
        $orderId = 'PPOB-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $biaya   = str_contains($request->payment_method, '_va') ? 4000 : 0;
        $total   = $produk['harga'] + $biaya;
        $kode    = $this->generatePaymentCode($request->payment_method);
        $expired = now()->addHour();

        DB::table('ppob_transaksi')->insert([
            'id_anggota'    => $id,
            'jenis_produk'  => $request->jenis_produk,
            'provider'      => $produk['provider'],
            'nomor_tujuan'  => $request->nomor_tujuan,
            'nominal'       => $produk['nominal'],
            'harga'         => $produk['harga'],
            'kode_produk'   => $produk['kode'],
            'nama_produk'   => $produk['nama'],
            'status'        => 'pending',
            'ref_id'        => $refId,
            'payment_ref'   => $orderId,
            'payment_method'=> $request->payment_method,
            'keterangan'    => 'Menunggu pembayaran',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $ppobId = DB::getPdo()->lastInsertId();

        DB::table('payment_gateway_transaksi')->insert([
            'id_anggota'       => $id,
            'order_id'         => $orderId,
            'jenis_pembayaran' => 'ppob',
            'ref_id'           => (string) $ppobId,
            'nominal'          => $produk['harga'],
            'biaya_admin'      => $biaya,
            'total_bayar'      => $total,
            'payment_method'   => $request->payment_method,
            'payment_code'     => $kode,
            'payment_expired'  => $expired,
            'status'           => 'pending',
            'keterangan'       => 'PPOB: ' . $produk['nama'],
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return response()->json([
            'message'  => 'Order berhasil dibuat!',
            'order_id' => $orderId,
            'payment'  => [
                'code'    => $kode,
                'method'  => $request->payment_method,
                'total'   => $total,
                'expired' => $expired->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /** GET /api/anggota/ppob/status/{orderId} */
    public function status(Request $request, string $orderId)
    {
        $id      = $this->idAnggota($request);
        $payment = DB::table('payment_gateway_transaksi')
            ->where('order_id', $orderId)->where('id_anggota', $id)->first();

        if (!$payment) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $transaksi = DB::table('ppob_transaksi')
            ->where('id_transaksi', $payment->ref_id)
            ->first();

        return response()->json(['payment' => $payment, 'transaksi' => $transaksi]);
    }

    /** POST /api/anggota/ppob/simulasi-bayar */
    public function simulasiBayar(Request $request)
    {
        $request->validate(['order_id' => 'required|string']);
        $id      = $this->idAnggota($request);
        $payment = DB::table('payment_gateway_transaksi')
            ->where('order_id', $request->order_id)->where('id_anggota', $id)->first();

        if (!$payment || $payment->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak valid.'], 422);
        }

        DB::table('payment_gateway_transaksi')->where('order_id', $request->order_id)->update([
            'status'  => 'paid', 'paid_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('ppob_transaksi')->where('id_transaksi', $payment->ref_id)->update([
            'status'     => 'success',
            'keterangan' => 'Pembayaran berhasil via simulasi',
            'updated_at' => now(),
        ]);

        $trx       = DB::table('ppob_transaksi')->find($payment->ref_id);
        $tokenKode = '';
        if ($trx && $trx->jenis_produk === 'token_listrik') {
            $tokenKode = implode('-', str_split(rand(10000000,99999999).rand(10000000,99999999), 4));
        }

        return response()->json(['success' => true, 'message' => 'Pembayaran berhasil!', 'token_kode' => $tokenKode]);
    }

    /** GET /api/anggota/ppob/riwayat */
    public function riwayat(Request $request)
    {
        $id   = $this->idAnggota($request);
        $data = DB::table('ppob_transaksi')->where('id_anggota', $id)->orderByDesc('created_at')->limit(50)->get();
        return response()->json(['riwayat' => $data]);
    }

    private function generatePaymentCode(string $method): string
    {
        if (str_contains($method, '_va')) {
            $prefix = ['bca_va'=>'10200','bni_va'=>'98890','mandiri_va'=>'70012','bri_va'=>'88801'];
            return ($prefix[$method] ?? '99900') . rand(1000000, 9999999);
        }
        if ($method === 'qris') {
            return 'QRIS-' . strtoupper(substr(md5(uniqid()), 0, 16));
        }
        return strtoupper($method) . '-' . rand(100000, 999999);
    }
}
