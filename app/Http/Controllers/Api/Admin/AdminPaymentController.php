<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
class AdminPaymentController extends Controller {
    public function index() { return response()->json(['data'=>DB::table('payment_gateway_transaksi')->paginate()]); }
    public function pendingCicilan() { return response()->json(['data'=>DB::table('pembayarans')->where('status','pending')->get()]); }
    public function verifikasi($id) {
        $row = DB::table('pembayarans')->where('id',$id)->first();
        DB::table('pembayarans')->where('id',$id)->update(['status'=>'diverifikasi','tgl_pembayaran'=>now()]);
        AuditLogger::log('pembayaran.verifikasi', new \App\Models\Pembayaran(['id'=>$id]), ['status'=>$row->status ?? null], ['status'=>'diverifikasi']);
        return response()->json(['message'=>'Pembayaran diverifikasi']);
    }
    public function tolak($id) {
        $row = DB::table('pembayarans')->where('id',$id)->first();
        DB::table('pembayarans')->where('id',$id)->update(['status'=>'gagal']);
        AuditLogger::log('pembayaran.tolak', new \App\Models\Pembayaran(['id'=>$id]), ['status'=>$row->status ?? null], ['status'=>'gagal']);
        return response()->json(['message'=>'Pembayaran ditolak']);
    }
}
