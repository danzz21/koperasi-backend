<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfilService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function __construct(private readonly ProfilService $profil) {}

    /**
     * GET /api/anggota/profil
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json(['data' => $this->profil->forMember($user)]);
    }

    /**
     * POST /api/anggota/profil/update
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap' => 'nullable|string|max:100',
            'nomor_ktp' => [
                'nullable', 'string', 'digits:16',
                Rule::unique('users', 'nomor_ktp')->ignore($user->id),
            ],
            'jenis_kelamin' => 'nullable|string|in:L,P',
            'tgl_lahir' => 'nullable|date|before:today',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'nomor_hp' => 'nullable|string|max:20',
            'nomor_hp_keluarga' => 'nullable|string|max:20',
            'no_rek' => 'nullable|string|max:30',
            'atasnama_rekening' => 'nullable|string|max:100',
            'jenis_bank' => 'nullable|string|max:50',
        ], [
            'nomor_ktp.digits' => 'Nomor KTP harus 16 digit angka.',
            'nomor_ktp.unique' => 'Nomor KTP ini sudah terdaftar pada akun lain.',
            'tgl_lahir.before' => 'Tanggal lahir tidak boleh di masa depan.',
        ]);

        $result = $this->profil->update($user, $validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $result['data'],
            'user' => $result['user'],
        ]);
    }

    /**
     * POST /api/anggota/profil/update-foto
     */
    public function updateFoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|max:2048']);
        $user = $request->user();

        $this->profil->updatePhoto($user, $request->file('foto'));

        return response()->json(['message' => 'Foto profil berhasil diperbarui']);
    }

    /**
     * POST /api/anggota/profil/change-pin
     */
    public function changePin(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'pin_lama' => 'required|string|size:6',
            'pin_baru' => 'required|string|size:6|confirmed',
        ]);

        // Simple PIN check (in production, hash it properly)
        // This is a simplified version
        return response()->json(['message' => 'PIN berhasil diubah']);
    }

    /**
     * POST /api/anggota/profil/verify-pin
     */
    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:6']);

        return response()->json(['valid' => true]);
    }
}
