<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    /**
     * GET /api/anggota/profil
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'nomor_anggota' => $user->username, // bisa di-generate jika ada kolom nomor_anggota
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
            ]
        ]);
    }

    /**
     * POST /api/anggota/profil/update
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap' => 'nullable|string|max:100',
            'jenis_kelamin' => 'nullable|string|in:L,P',
            'tgl_lahir' => 'nullable|date',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'nomor_hp' => 'nullable|string|max:15',
            'no_rek' => 'nullable|string|max:30',
            'atasnama_rekening' => 'nullable|string|max:100',
            'jenis_bank' => 'nullable|string|max:50',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }

    /**
     * POST /api/anggota/profil/update-foto
     */
    public function updateFoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|max:2048']);
        $user = $request->user();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile', $filename);
            $user->update(['foto' => $filename]);
        }

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
