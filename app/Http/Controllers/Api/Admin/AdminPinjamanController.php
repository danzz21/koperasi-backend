<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminPinjamanController extends Controller {
    public function index() { return response()->json(['data'=>DB::table('pinjamen')->paginate()]); }
    public function pending() { return response()->json(['data'=>DB::table('pinjamen')->where('status','ditolak')->get()]); }
    public function approve($jenis,$id) { DB::table('pinjamen')->where('id',$id)->update(['status'=>'aktif','tgl_persetujuan'=>now()->toDateString()]); return response()->json(['message'=>'Disetujui']); }
    public function reject($jenis,$id) { return response()->json(['message'=>'Ditolak']); }
}
