<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        // Cari user by username atau email
        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        if ($user->status === 'pending') {
            return response()->json(['message' => 'Akun Anda belum diverifikasi oleh admin.'], 403);
        }

        if ($user->status === 'rejected') {
            return response()->json(['message' => 'Akun Anda ditolak oleh admin.'], 403);
        }

        // Revoke token lama
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('koperasi-token', [$user->role])->plainTextToken;

        return response()->json([
            'token'    => $token,
            'token_type' => 'Bearer',
            'user'     => [
                'id'       => $user->id,
                'nama'     => $user->nama_lengkap,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
                'status'   => $user->status,
            ],
        ]);
    }

    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'username'     => 'required|string|unique:users,username|min:4|max:30',
            'password'     => 'required|string|min:8|confirmed',
            'nomor_ktp'    => 'required|string|size:16|unique:users,nomor_ktp',
            'nomor_hp'     => 'required|string|max:15',
            'jenis_kelamin' => 'nullable|string|in:L,P',
            'pekerjaan'    => 'nullable|string|max:100',
            'alamat'       => 'nullable|string|max:500',
            'no_rek'       => 'nullable|string|max:30',
            'atasnama_rekening' => 'nullable|string|max:100',
            'jenis_bank'   => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email'        => $request->email,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'nomor_ktp'    => $request->nomor_ktp,
            'nomor_hp'     => $request->nomor_hp,
            'jenis_kelamin' => $request->jenis_kelamin,
            'pekerjaan'    => $request->pekerjaan,
            'alamat'       => $request->alamat,
            'no_rek'       => $request->no_rek,
            'atasnama_rekening' => $request->atasnama_rekening,
            'jenis_bank'   => $request->jenis_bank,
            'role'         => 'anggota',
            'status'       => 'pending',
        ]);

        return response()->json([
            'message' => 'Pendaftaran berhasil! Menunggu verifikasi admin.',
            'user_id' => $user->id,
        ], 201);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        // Implementasi minimal — kirim email reset (opsional untuk now)
        return response()->json(['message' => 'Fitur reset password akan segera tersedia.'], 200);
    }
}
