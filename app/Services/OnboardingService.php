<?php

namespace App\Services;

use App\Models\User;
use App\Support\AuthUserPayload;

class OnboardingService
{
    /**
     * @return array<string, mixed>
     */
    public function onboardingData(User $user): array
    {
        return [
            'user' => AuthUserPayload::for($user),
            'needs_onboarding' => $user->requiresProfileCompletion(),
            'missing_fields' => $user->missingProfileFields(),
            'required_fields' => array_keys(AuthUserPayload::REQUIRED_PROFILE_FIELDS),
            'profile' => $this->profilePayload($user),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function complete(User $user, array $data): User
    {
        $password = $data['password'] ?? null;
        unset($data['password']);

        $user->fill($data);

        if (is_string($password) && $password !== '') {
            $user->password = $password;
        }

        $user->save();

        return $user->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function profilePayload(User $user): array
    {
        return [
            'nama_lengkap' => $user->nama_lengkap,
            'nomor_anggota' => $user->username,
            'email' => $user->email,
            'nomor_ktp' => $user->nomor_ktp,
            'nomor_hp' => $user->nomor_hp,
            'nomor_hp_keluarga' => $user->nomor_hp_keluarga,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tgl_lahir' => $user->tgl_lahir,
            'pekerjaan' => $user->pekerjaan,
            'alamat' => $user->alamat,
            'no_rek' => $user->no_rek,
            'atasnama_rekening' => $user->atasnama_rekening,
            'jenis_bank' => $user->jenis_bank,
        ];
    }
}
