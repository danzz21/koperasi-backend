<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthUserPayload;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'ID token Google wajib dikirim.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $payload = $this->verifyIdToken($request->input('credential'));

        if (! $payload) {
            return response()->json(['message' => 'Token Google tidak valid atau sudah kedaluwarsa.'], 401);
        }

        // Pastikan token memang ditujukan untuk aplikasi ini (kalau client ID di-set)
        $expectedAudience = config('services.google.client_id');
        if (! empty($expectedAudience) && ($payload['aud'] ?? null) !== $expectedAudience) {
            return response()->json(['message' => 'Token Google bukan untuk aplikasi ini.'], 401);
        }

        $email = $payload['email'] ?? null;
        $sub   = $payload['sub'] ?? null;

        if (! $email || ! $sub) {
            return response()->json(['message' => 'Data akun Google tidak lengkap.'], 422);
        }

        if (($payload['email_verified'] ?? 'false') !== 'true') {
            return response()->json(['message' => 'Email Google belum diverifikasi.'], 403);
        }

        $user = User::where('google_id', $sub)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            // Akun lama: tautkan google_id
            if (! $user->google_id) {
                $user->google_id = $sub;
                $user->save();
            }

            if ($user->role !== 'anggota') {
                return response()->json([
                    'message' => 'Akun ini bukan akun anggota. Silakan gunakan panel admin.',
                ], 403);
            }
        } else {
            // Akun baru dari Google — otomatis jadi anggota aktif
            $user = User::create([
                'nama_lengkap' => $payload['name'] ?? $email,
                'email'        => $email,
                'username'     => $this->makeUsername($payload['name'] ?? null, $email),
                'password'     => Hash::make(Str::random(40)),
                // Kolom ini NOT NULL di tabel users → isi placeholder unik
                'nomor_ktp'    => $this->makePlaceholderKtp($sub),
                'nomor_hp'     => '-',
                'avatar'       => $payload['picture'] ?? null,
                'google_id'    => $sub,
                'role'         => 'anggota',
                'status'       => 'aktif',
            ]);
        }

        if ($user->status !== 'aktif') {
            return response()->json([
                'message' => 'Akun Anda belum aktif. Hubungi admin koperasi.',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('koperasi-google-token', ['anggota'])->plainTextToken;

        return response()->json([
            'message' => 'Login Google berhasil.',
            'token'   => $token,
            'token_type' => 'Bearer',
            'user'    => AuthUserPayload::for($user->refresh()),
        ]);
    }

    /**
     * Verifikasi ID token ke Google. Mengembalikan array payload atau null bila gagal.
     */
    private function verifyIdToken(string $idToken): ?array
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get(self::TOKENINFO_URL, ['id_token' => $idToken]);
        } catch (ConnectionException $e) {
            report($e);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || empty($payload['sub'])) {
            return null;
        }

        // Cek masa berlaku token
        if (isset($payload['exp']) && (int) $payload['exp'] < now()->timestamp) {
            return null;
        }

        return $payload;
    }

    /**
     * Nomor KTP wajib unik & NOT NULL. Untuk akun Google, pakai placeholder
     * berbasis Google `sub` supaya tidak bentrok antar akun.
     */
    private function makePlaceholderKtp(string $googleSub): string
    {
        $base = 'G' . substr(preg_replace('/[^0-9]/', '', $googleSub) ?: '0', -15);
        $candidate = $base;
        $counter = 1;

        while (User::where('nomor_ktp', $candidate)->exists()) {
            $candidate = $base . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function makeUsername(?string $name, string $email): string
    {
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', $name ?? '') ?: 'user';
        $base = strtolower(substr($base, 0, 15)) ?: 'user';

        if (! User::where('username', $base)->exists()) {
            return $base;
        }

        // Fallback unik dari email
        $fromEmail = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', Str::before($email, '@')) ?: 'user');
        $fromEmail = substr($fromEmail, 0, 15);

        $candidate = $fromEmail;
        $counter   = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $fromEmail . $counter;
            $counter++;
        }

        return $candidate;
    }
}

