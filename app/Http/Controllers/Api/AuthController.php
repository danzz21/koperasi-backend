<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

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

        $result = $this->auth->login($request->username, $request->password);

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username|min:4|max:30',
            'password' => 'required|string|min:8|confirmed',
            'nomor_ktp' => 'required|string|size:16|unique:users,nomor_ktp',
            'nomor_hp' => 'required|string|max:15',
            'jenis_kelamin' => 'nullable|string|in:L,P',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'no_rek' => 'nullable|string|max:30',
            'atasnama_rekening' => 'nullable|string|max:100',
            'jenis_bank' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $user = $this->auth->register($validator->validated());

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
        $this->auth->logout($request->user());

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        return response()->json($this->auth->currentUser($request->user()));
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
