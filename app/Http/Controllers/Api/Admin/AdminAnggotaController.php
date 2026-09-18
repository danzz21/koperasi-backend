<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class AdminAnggotaController extends Controller {
    public function index() { return response()->json(['data' => User::where('role','anggota')->paginate()]); }
    public function store(Request $request) { return response()->json(['data' => $request->all()], 201); }
    public function show($id) { return response()->json(['data' => User::findOrFail($id)]); }
    public function update(Request $request, $id) { $u=User::findOrFail($id); $u->update($request->only(['nama_lengkap','status','nomor_hp'])); return response()->json(['data'=>$u]); }
    public function destroy($id) { User::findOrFail($id)->delete(); return response()->noContent(); }
    public function verify($id) { User::whereKey($id)->update(['status'=>'aktif']); return response()->json(['message'=>'Anggota diverifikasi']); }
    public function reject($id) { User::whereKey($id)->update(['status'=>'nonaktif']); return response()->json(['message'=>'Anggota ditolak']); }
    public function pending() { return response()->json(['data'=>User::where('role','anggota')->where('status','pending')->get()]); }
}
