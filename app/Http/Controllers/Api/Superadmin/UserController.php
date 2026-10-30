<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\SuperadminUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(private readonly SuperadminUserService $users) {}

    /**
     * GET /api/superadmin/users
     */
    public function index(Request $request)
    {
        return response()->json(['data' => $this->users->listing($request->only(['role', 'status', 'q']))]);
    }

    /**
     * POST /api/superadmin/users
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|min:4|max:30|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:superadmin,admin,anggota',
            'status' => 'nullable|in:pending,aktif,nonaktif',
            'nomor_ktp' => 'nullable|string|size:16|unique:users,nomor_ktp',
            'nomor_hp' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $user = $this->users->create($validator->validated());

        return response()->json(['message' => 'User berhasil dibuat.', 'data' => $user], 201);
    }

    /**
     * GET /api/superadmin/users/{id}
     */
    public function show($id)
    {
        return response()->json(['data' => $this->users->find($id)]);
    }

    /**
     * PUT /api/superadmin/users/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'username' => 'sometimes|string|min:4|max:30|unique:users,username,'.$id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:superadmin,admin,anggota',
            'status' => 'sometimes|in:pending,aktif,nonaktif',
            'nomor_hp' => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $user = $this->users->update($this->users->find($id), $validator->validated());

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

        $user = $this->users->updateRole(
            $this->users->find($id),
            $validator->validated()['role'],
        );

        return response()->json([
            'message' => "Role {$user->nama_lengkap} berhasil diubah menjadi {$user->role}.",
            'data' => $user,
        ]);
    }

    /**
     * DELETE /api/superadmin/users/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $this->users->find($id);

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun sendiri.'], 422);
        }

        $this->users->delete($user);

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
