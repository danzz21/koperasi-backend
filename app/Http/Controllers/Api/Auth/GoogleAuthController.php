<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Login Google untuk ANGGOTA (frontend React).
 *
 * Verifikasi dilakukan langsung ke endpoint Google (tokeninfo) memakai Guzzle/Http
 * bawaan Laravel — jadi tidak perlu package tambahan (mis. Socialite).
 *
 * Frontend mengirim ID Token (JWT) hasil Google Identity Services:
 *   POST /api/auth/google  { "credential": "<id_token>" }
 */
class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleAuthService $googleAuth) {}

    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'ID token Google wajib dikirim.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->googleAuth->login($request->input('credential'));

        return response()->json($result['payload'], $result['status']);
    }
}
