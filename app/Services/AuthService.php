<?php

namespace App\Services;

use App\Models\User;
use App\Support\AuthUserPayload;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @return array{status: int, payload: array<string, mixed>}
     */
    public function login(string $username, string $password): array
    {
        $user = User::query()
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return [
                'status' => 401,
                'payload' => ['message' => 'Username atau password salah.'],
            ];
        }

        if ($user->status === 'pending') {
            return [
                'status' => 403,
                'payload' => ['message' => 'Akun Anda belum diverifikasi oleh admin.'],
            ];
        }

        if (in_array($user->status, ['nonaktif', 'rejected'], true)) {
            return [
                'status' => 403,
                'payload' => ['message' => 'Akun Anda dinonaktifkan oleh admin.'],
            ];
        }

        $user->tokens()->delete();
        $token = $user->createToken('koperasi-token', [$user->role])->plainTextToken;

        return [
            'status' => 200,
            'payload' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => AuthUserPayload::for($user->fresh()),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): User
    {
        return User::query()->create([
            'nama_lengkap' => $data['nama_lengkap'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'nomor_ktp' => $data['nomor_ktp'],
            'nomor_hp' => $data['nomor_hp'],
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'pekerjaan' => $data['pekerjaan'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'no_rek' => $data['no_rek'] ?? null,
            'atasnama_rekening' => $data['atasnama_rekening'] ?? null,
            'jenis_bank' => $data['jenis_bank'] ?? null,
            'role' => 'anggota',
            'status' => 'pending',
        ]);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * @return array{user: array<string, mixed>}
     */
    public function currentUser(User $user): array
    {
        return ['user' => AuthUserPayload::for($user)];
    }
}
