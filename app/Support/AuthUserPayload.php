<?php

namespace App\Support;

use App\Models\User;

/**
 * Bentuk payload user yang dikirim ke frontend (SPA React).
 *
 * Dipakai oleh AuthController, GoogleAuthController, dan PasskeyAuthController
 * supaya semua jalur login menghasilkan struktur yang sama — termasuk penanda
 * apakah anggota masih wajib melengkapi data (onboarding).
 */
class AuthUserPayload
{
    /**
     * Field wajib yang harus terisi sebelum anggota bisa memakai fitur penuh.
     *
     * @var array<string, string>
     */
    public const REQUIRED_PROFILE_FIELDS = [
        'nama_lengkap'  => 'Nama lengkap',
        'nomor_ktp'     => 'Nomor KTP',
        'nomor_hp'      => 'Nomor HP',
        'jenis_kelamin' => 'Jenis kelamin',
        'tgl_lahir'     => 'Tanggal lahir',
        'pekerjaan'     => 'Pekerjaan',
        'alamat'        => 'Alamat',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function for(User $user): array
    {
        return [
            'id'                => $user->id,
            'nama'              => $user->nama_lengkap,
            'username'          => $user->username,
            'email'             => $user->email,
            'nomor_hp'          => $user->nomor_hp,
            'avatar'            => $user->avatar,
            'foto'              => $user->foto,
            'role'              => $user->role,
            'status'            => $user->status,
            'profile_completed' => $user->hasCompletedProfile(),
            'needs_onboarding'  => $user->requiresProfileCompletion(),
            'missing_fields'    => $user->missingProfileFields(),
        ];
    }
}
