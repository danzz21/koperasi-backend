<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminSimpananController extends Controller {
    public function index() { return response()->json(['data'=>DB::table('simpanans')->paginate()]); }
    public function pending() { return response()->json(['data'=>DB::table('simpanans')->where('status','tidak_aktif')->get()]); }
    public function approve($jenis,$id) { DB::table('simpanans')->where('id',$id)->update(['status'=>'aktif']); return response()->json(['message'=>'Disetujui']); }
    public function reject($jenis,$id) { return response()->json(['message'=>'Ditolak']); }
}
