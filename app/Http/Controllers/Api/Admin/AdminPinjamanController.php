<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
class AdminPinjamanController extends Controller {
    public function index() { return response()->json(['data'=>DB::table('pinjamen')->paginate()]); }
    public function pending() { return response()->json(['data'=>DB::table('pinjamen')->where('status','ditolak')->get()]); }
    public function approve($jenis,$id) {
        $row = DB::table('pinjamen')->where('id',$id)->first();
        DB::table('pinjamen')->where('id',$id)->update(['status'=>'aktif','tgl_persetujuan'=>now()->toDateString()]);
        AuditLogger::log('pinjaman.approve', new \App\Models\Pinjaman(['id'=>$id]), ['status'=>$row->status ?? null, 'jenis'=>$jenis], ['status'=>'aktif']);
        return response()->json(['message'=>'Disetujui']);
    }
    public function reject($jenis,$id) {
        $row = DB::table('pinjamen')->where('id',$id)->first();
        DB::table('pinjamen')->where('id',$id)->update(['status'=>'ditolak']);
        AuditLogger::log('pinjaman.reject', new \App\Models\Pinjaman(['id'=>$id]), ['status'=>$row->status ?? null, 'jenis'=>$jenis], ['status'=>'ditolak']);
        return response()->json(['message'=>'Ditolak']);
    }
}
