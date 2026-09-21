<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * GET /api/superadmin/users
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return response()->json(['data' => $query->latest()->paginate(15)]);
    }

    /**
     * POST /api/superadmin/users
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|min:4|max:30|unique:users,username',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8',
            'role'         => 'required|in:superadmin,admin,anggota',
            'status'       => 'nullable|in:pending,aktif,nonaktif',
            'nomor_ktp'    => 'nullable|string|size:16|unique:users,nomor_ktp',
            'nomor_hp'     => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'status'       => $request->status ?? 'aktif',
            'nomor_ktp'    => $request->nomor_ktp,
            'nomor_hp'     => $request->nomor_hp,
        ]);

        AuditLogger::log('user.create', $user, [], [
            'username' => $user->username,
            'role'     => $user->role,
        ]);

        return response()->json(['message' => 'User berhasil dibuat.', 'data' => $user], 201);
    }

    /**
     * GET /api/superadmin/users/{id}
     */
    public function show($id)
    {
        return response()->json(['data' => User::findOrFail($id)]);
    }

    /**
     * PUT /api/superadmin/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string|max:100',
            'email'        => 'sometimes|email|unique:users,email,' . $user->id,
            'username'     => 'sometimes|string|min:4|max:30|unique:users,username,' . $user->id,
            'password'     => 'sometimes|string|min:8',
            'role'         => 'sometimes|in:superadmin,admin,anggota',
            'status'       => 'sometimes|in:pending,aktif,nonaktif',
            'nomor_hp'     => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $old = $user->only(['username', 'email', 'role', 'status']);

        $data = $request->only(['nama_lengkap', 'email', 'username', 'role', 'status', 'nomor_hp']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        AuditLogger::log('user.update', $user, $old, $user->only(['username', 'email', 'role', 'status']));

        return response()->json(['message' => 'User berhasil diubah.', 'data' => $user]);
    }

    /**
     * PUT /api/superadmin/users/{id}/role
     */
    public function updateRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:superadmin,admin,anggota',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($id);
        $old  = $user->role;

        $user->update(['role' => $request->role]);

        AuditLogger::log('user.update_role', $user, ['role' => $old], ['role' => $user->role]);

        return response()->json([
            'message' => "Role {$user->nama_lengkap} berhasil diubah menjadi {$user->role}.",
            'data'    => $user,
        ]);
    }

    /**
     * DELETE /api/superadmin/users/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun sendiri.'], 422);
        }

        AuditLogger::log('user.delete', $user, $user->only(['username', 'email', 'role']));

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
