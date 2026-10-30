<?php

namespace App\Services;

use App\Models\User;
use App\Support\AuthUserPayload;
use Illuminate\Http\UploadedFile;

class ProfilService
{
    /**
     * @return array<string, mixed>
     */
    public function forMember(User $user): array
    {
        return [
            'id' => $user->id,
            'nama_lengkap' => $user->nama_lengkap,
            'nomor_anggota' => $user->username,
            'email' => $user->email,
            'no_ktp' => $user->nomor_ktp,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tgl_lahir' => $user->tgl_lahir ?? null,
            'pekerjaan' => $user->pekerjaan,
            'no_hp' => $user->nomor_hp,
            'alamat' => $user->alamat,
            'no_rek' => $user->no_rek,
            'atasnama_rekening' => $user->atasnama_rekening,
            'jenis_bank' => $user->jenis_bank,
            'status' => $user->status,
            'tgl_bergabung' => $user->created_at,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{data: User, user: array<string, mixed>}
     */
    public function update(User $user, array $data): array
    {
        $user->update($data);
        $user = $user->fresh();

        return [
            'data' => $user,
            'user' => AuthUserPayload::for($user),
        ];
    }

    public function updatePhoto(User $user, UploadedFile $photo): void
    {
        $filename = time().'_'.$photo->getClientOriginalName();
        $photo->storeAs('public/profile', $filename);
        $user->update(['foto' => $filename]);
    }
}
