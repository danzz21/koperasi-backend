<?php

namespace App\Services;

use App\Models\User;
use App\Support\AuthUserPayload;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthService
{
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    /**
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function login(string $credential): array
    {
        $payload = $this->verifyIdToken($credential);

        if (! $payload) {
            return ['status' => 401, 'payload' => ['message' => 'Token Google tidak valid atau sudah kedaluwarsa.']];
        }

        $expectedAudience = config('services.google.client_id');
        if (! empty($expectedAudience) && ($payload['aud'] ?? null) !== $expectedAudience) {
            return ['status' => 401, 'payload' => ['message' => 'Token Google bukan untuk aplikasi ini.']];
        }

        $email = $payload['email'] ?? null;
        $sub = $payload['sub'] ?? null;

        if (! $email || ! $sub) {
            return ['status' => 422, 'payload' => ['message' => 'Data akun Google tidak lengkap.']];
        }

        if (($payload['email_verified'] ?? 'false') !== 'true') {
            return ['status' => 403, 'payload' => ['message' => 'Email Google belum diverifikasi.']];
        }

        $user = User::query()->where('google_id', $sub)->first()
            ?? User::query()->where('email', $email)->first();

        if ($user) {
            if (! $user->google_id) {
                $user->google_id = $sub;
                $user->save();
            }

            if ($user->role !== 'anggota') {
                return [
                    'status' => 403,
                    'payload' => ['message' => 'Akun ini bukan akun anggota. Silakan gunakan panel admin.'],
                ];
            }
        } else {
            $user = User::query()->create([
                'nama_lengkap' => $payload['name'] ?? $email,
                'email' => $email,
                'username' => $this->makeUsername($payload['name'] ?? null, $email),
                'password' => Hash::make(Str::random(40)),
                'nomor_ktp' => $this->makePlaceholderKtp($sub),
                'nomor_hp' => '-',
                'avatar' => $payload['picture'] ?? null,
                'google_id' => $sub,
                'role' => 'anggota',
                'status' => 'aktif',
            ]);
        }

        if ($user->status !== 'aktif') {
            return ['status' => 403, 'payload' => ['message' => 'Akun Anda belum aktif. Hubungi admin koperasi.']];
        }

        $user->tokens()->delete();
        $token = $user->createToken('koperasi-google-token', ['anggota'])->plainTextToken;

        return [
            'status' => 200,
            'payload' => [
                'message' => 'Login Google berhasil.',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => AuthUserPayload::for($user->fresh()),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function verifyIdToken(string $idToken): ?array
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get(self::TOKENINFO_URL, ['id_token' => $idToken]);
        } catch (ConnectionException $exception) {
            report($exception);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (! is_array($payload) || empty($payload['sub'])) {
            return null;
        }

        if (isset($payload['exp']) && (int) $payload['exp'] < now()->timestamp) {
            return null;
        }

        return $payload;
    }

    private function makePlaceholderKtp(string $googleSub): string
    {
        $base = 'G'.substr(preg_replace('/[^0-9]/', '', $googleSub) ?: '0', -15);
        $candidate = $base;
        $counter = 1;

        while (User::query()->where('nomor_ktp', $candidate)->exists()) {
            $candidate = $base.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function makeUsername(?string $name, string $email): string
    {
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', $name ?? '') ?: 'user';
        $base = strtolower(substr($base, 0, 15)) ?: 'user';

        if (! User::query()->where('username', $base)->exists()) {
            return $base;
        }

        $fromEmail = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', Str::before($email, '@')) ?: 'user');
        $fromEmail = substr($fromEmail, 0, 15);
        $candidate = $fromEmail;
        $counter = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = $fromEmail.$counter;
            $counter++;
        }

        return $candidate;
    }
}
