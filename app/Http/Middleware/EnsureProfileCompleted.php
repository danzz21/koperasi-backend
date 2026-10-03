<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan anggota yang belum melengkapi data (mis. hasil login Google) agar
 * tidak dapat memakai fitur anggota sebelum menyelesaikan onboarding.
 *
 * Frontend mengenali balasan ini lewat `code: PROFILE_INCOMPLETE` lalu
 * mengarahkan pengguna ke halaman /anggota/onboarding.
 */
class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Akun dinonaktifkan oleh admin → blokir semua endpoint anggota,
            // termasuk kalau masih ada token lama di perangkat.
            if (in_array($user->status, ['nonaktif', 'rejected'], true)) {
                return response()->json([
                    'message' => 'Akun Anda dinonaktifkan oleh admin.',
                    'code'    => 'ACCOUNT_DISABLED',
                ], 403);
            }

            if ($user->requiresProfileCompletion()) {
                return response()->json([
                    'message'        => 'Lengkapi data profil Anda terlebih dahulu sebelum menggunakan fitur ini.',
                    'code'           => 'PROFILE_INCOMPLETE',
                    'needs_onboarding' => true,
                    'missing_fields' => $user->missingProfileFields(),
                ], 403);
            }
        }

        return $next($request);
    }
}
