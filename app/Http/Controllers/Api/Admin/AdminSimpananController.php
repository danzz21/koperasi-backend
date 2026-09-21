<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
class AdminSimpananController extends Controller {
    public function index() { return response()->json(['data'=>DB::table('simpanans')->paginate()]); }
    public function pending() { return response()->json(['data'=>DB::table('simpanans')->where('status','tidak_aktif')->get()]); }
    public function approve($jenis,$id) {
        $row = DB::table('simpanans')->where('id',$id)->first();
        DB::table('simpanans')->where('id',$id)->update(['status'=>'aktif']);
        AuditLogger::log('simpanan.approve', new \App\Models\Simpanan(['id'=>$id]), ['status'=>$row->status ?? null, 'jenis'=>$jenis], ['status'=>'aktif']);
        return response()->json(['message'=>'Disetujui']);
    }
    public function reject($jenis,$id) {
        $row = DB::table('simpanans')->where('id',$id)->first();
        DB::table('simpanans')->where('id',$id)->update(['status'=>'ditolak']);
        AuditLogger::log('simpanan.reject', new \App\Models\Simpanan(['id'=>$id]), ['status'=>$row->status ?? null, 'jenis'=>$jenis], ['status'=>'ditolak']);
        return response()->json(['message'=>'Ditolak']);
    }
}
