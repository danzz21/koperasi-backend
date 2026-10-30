<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $payments) {}

    public function riwayat(Request $request)
    {
        return response()->json(['data' => $this->payments->history($request->user())]);
    }

    public function status(Request $request, string $orderId)
    {
        $payment = $this->payments->findPayment($request->user(), $orderId);

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
        $payment = $this->payments->simulatePayment($request->user(), $data['order_id']);

        return response()->json(['success' => true, 'data' => $payment]);
    }

    public function getMetode()
    {
        return response()->json(['metode' => [
            ['kode' => 'bca_va', 'nama' => 'BCA Virtual Account'],
            ['kode' => 'bni_va', 'nama' => 'BNI Virtual Account'],
            ['kode' => 'bri_va', 'nama' => 'BRI Virtual Account'],
            ['kode' => 'qris', 'nama' => 'QRIS'],
            ['kode' => 'gopay', 'nama' => 'GoPay'],
            ['kode' => 'dana', 'nama' => 'DANA'],
        ]]);
    }

    private function create(Request $request, string $jenis)
    {
        $data = $request->validate([
            'nominal' => 'required|numeric|min:1',
            'payment_method' => 'required|in:bca_va,bni_va,bri_va,qris,gopay,dana',
        ]);

        if ($jenis === 'cicilan') {
            $data['id'] = $request->validate(['id' => 'required|integer'])['id'];
        }

        if ($jenis === 'simpanan') {
            $data['jenis'] = $request->validate(['jenis' => 'required|in:pokok,wajib,sukarela'])['jenis'];
        }
        $orderId = $this->payments->create($request->user(), $jenis, $data);

        return response()->json(['order_id' => $orderId], 201);
    }
}
