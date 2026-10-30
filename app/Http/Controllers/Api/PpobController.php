<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PpobService;
use Illuminate\Http\Request;

class PpobController extends Controller
{
    public const EWALLET = PpobService::EWALLET;

    public function __construct(private readonly PpobService $ppob) {}

    /** GET /api/anggota/ppob */
    public function index(Request $request)
    {
        return response()->json($this->ppob->overview($request->user()));
    }

    /** GET /api/anggota/ppob/produk/pulsa */
    public function produkPulsa()
    {
        return response()->json($this->ppob->products('pulsa'));
    }

    public function produkData()
    {
        return response()->json($this->ppob->products('paket_data'));
    }

    public function produkListrik()
    {
        return response()->json($this->ppob->products('token_listrik'));
    }

    public function produkEwallet()
    {
        return response()->json($this->ppob->products('ewallet'));
    }

    /** POST /api/anggota/ppob/order */
    public function order(Request $request)
    {
        $data = $request->validate([
            'kode_produk' => 'required|string',
            'jenis_produk' => 'required|in:pulsa,paket_data,token_listrik,ewallet',
            'nomor_tujuan' => 'required|string|min:8|max:20',
            'payment_method' => 'required|string',
        ]);

        $result = $this->ppob->createOrder($request->user(), $data);

        return response()->json($result['payload'], $result['status']);
    }

    /** GET /api/anggota/ppob/status/{orderId} */
    public function status(Request $request, string $orderId)
    {
        $result = $this->ppob->status($request->user(), $orderId);

        return response()->json($result['payload'], $result['status']);
    }

    /** POST /api/anggota/ppob/simulasi-bayar */
    public function simulasiBayar(Request $request)
    {
        $data = $request->validate(['order_id' => 'required|string']);
        $result = $this->ppob->simulatePayment($request->user(), $data['order_id']);

        return response()->json($result['payload'], $result['status']);
    }

    /** GET /api/anggota/ppob/riwayat */
    public function riwayat(Request $request)
    {
        return response()->json(['riwayat' => $this->ppob->history($request->user())]);
    }
}
