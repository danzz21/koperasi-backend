<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminPaymentController extends Controller {
    public function index() { return response()->json(['data'=>DB::table('payment_gateway_transaksi')->paginate()]); }
    public function pendingCicilan() { return response()->json(['data'=>DB::table('pembayarans')->where('status','pending')->get()]); }
    public function verifikasi($id) { DB::table('pembayarans')->where('id',$id)->update(['status'=>'diverifikasi','tgl_pembayaran'=>now()]); return response()->json(['message'=>'Pembayaran diverifikasi']); }
    public function tolak($id) { DB::table('pembayarans')->where('id',$id)->update(['status'=>'gagal']); return response()->json(['message'=>'Pembayaran ditolak']); }
}
