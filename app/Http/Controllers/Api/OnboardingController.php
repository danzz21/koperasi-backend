<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AuthUserPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Onboarding anggota — melengkapi data setelah login Google.
 *
 * Akun yang dibuat lewat Google hanya punya nama + email; nomor KTP dan nomor HP
 * diisi placeholder, sehingga anggota wajib melengkapi data sebelum bisa
 * memakai fitur apa pun (dijaga middleware `profile.completed`).
 */
class OnboardingController extends Controller
{
    /** GET /api/anggota/onboarding — data awal untuk form onboarding */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user'             => AuthUserPayload::for($user),
            'needs_onboarding' => $user->requiresProfileCompletion(),
            'missing_fields'   => $user->missingProfileFields(),
            'required_fields'  => array_keys(AuthUserPayload::REQUIRED_PROFILE_FIELDS),
            'profile'          => $this->profilePayload($user),
        ]);
    }

    /** POST /api/anggota/onboarding — simpan data & selesaikan onboarding */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap'      => 'required|string|max:100',
            'nomor_ktp'         => [
                'required', 'string', 'digits:16',
                Rule::unique('users', 'nomor_ktp')->ignore($user->id),
            ],
            'nomor_hp'          => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{9,20}$/'],
            'nomor_hp_keluarga' => 'nullable|string|max:20',
            'jenis_kelamin'     => 'required|string|in:L,P',
            'tgl_lahir'         => 'required|date|before:today',
            'pekerjaan'         => 'required|string|max:100',
            'alamat'            => 'required|string|max:500',
            'no_rek'            => 'nullable|string|max:30',
            'atasnama_rekening' => 'nullable|string|max:100',
            'jenis_bank'        => 'nullable|string|max:50',
            'password'          => 'nullable|string|min:8|confirmed',
        ], [
            'nomor_ktp.digits'   => 'Nomor KTP harus 16 digit angka.',
            'nomor_ktp.unique'   => 'Nomor KTP ini sudah terdaftar pada akun lain.',
            'nomor_hp.regex'     => 'Nomor HP tidak valid (contoh: 081234567890).',
            'tgl_lahir.before'   => 'Tanggal lahir tidak boleh di masa depan.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        // Password tidak mass-assign; di-set manual (cast "hashed" meng-hash otomatis)
        $password = $validated['password'] ?? null;
        unset($validated['password']);

        $user->fill($validated);

        if (is_string($password) && $password !== '') {
            $user->password = $password;
        }

        // Bila semua field wajib sudah terisi, model menandai profil lengkap sendiri
        $user->save();

        $user->refresh();

        return response()->json([
            'message' => 'Data berhasil dilengkapi. Selamat menggunakan aplikasi!',
            'user'    => AuthUserPayload::for($user),
            'profile' => $this->profilePayload($user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload($user): array
    {
        return [
            'nama_lengkap'      => $user->nama_lengkap,
            'nomor_anggota'     => $user->username,
            'email'             => $user->email,
            'nomor_ktp'         => $user->nomor_ktp,
            'nomor_hp'          => $user->nomor_hp,
            'nomor_hp_keluarga' => $user->nomor_hp_keluarga,
            'jenis_kelamin'     => $user->jenis_kelamin,
            'tgl_lahir'         => $user->tgl_lahir,
            'pekerjaan'         => $user->pekerjaan,
            'alamat'            => $user->alamat,
            'no_rek'            => $user->no_rek,
            'atasnama_rekening' => $user->atasnama_rekening,
            'jenis_bank'        => $user->jenis_bank,
        ];
    }
}
