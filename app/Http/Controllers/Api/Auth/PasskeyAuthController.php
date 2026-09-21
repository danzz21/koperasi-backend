<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use App\Support\WebAuthn\Base64Url;
use App\Support\WebAuthn\WebAuthnException;
use App\Support\WebAuthn\WebAuthnVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Passkey (WebAuthn) untuk anggota.
 *
 * Alur:
 *   1. Registrasi perangkat (butuh login): POST /api/auth/passkey/register/options
 *                                        POST /api/auth/passkey/register/verify
 *   2. Login biometrik (public)         : POST /api/auth/passkey/login/options
 *                                        POST /api/auth/passkey/login/verify
 *   3. Kelola perangkat                 : GET/DELETE /api/auth/passkey
 *
 * Challenge disimpan di cache (5 menit, sekali pakai) agar tidak bisa diputar ulang.
 */
class PasskeyAuthController extends Controller
{
    private const CHALLENGE_TTL_MINUTES = 5;

    private const SUPPORTED_ALGORITHMS = [
        ['type' => 'public-key', 'alg' => -7],   // ES256
        ['type' => 'public-key', 'alg' => -257], // RS256
    ];

    /* ───────────────────────── Registrasi (butuh login) ───────────────────────── */

    public function registerOptions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Anda harus login sebelum mengaktifkan sidik jari.'], 401);
        }

        $challenge = $this->newChallenge();
        Cache::put($this->registerCacheKey($user->id), $challenge, now()->addMinutes(self::CHALLENGE_TTL_MINUTES));

        $excludeCredentials = Passkey::where('user_id', $user->id)
            ->pluck('credential_id')
            ->map(fn (string $id) => ['type' => 'public-key', 'id' => $id])
            ->values()
            ->all();

        return response()->json([
            'options' => [
                'challenge'              => $challenge,
                'rp'                     => [
                    'id'   => $this->rpId($request),
                    'name' => (string) config('services.webauthn.rp_name', 'K-Samara'),
                ],
                'user'                   => [
                    'id'          => Base64Url::encode((string) $user->id),
                    'name'        => $user->username ?: $user->email,
                    'displayName' => $user->nama_lengkap ?: ($user->username ?: $user->email),
                ],
                'pubKeyCredParams'       => self::SUPPORTED_ALGORITHMS,
                'timeout'                => 60000,
                'attestation'            => 'none',
                'authenticatorSelection' => [
                    'residentKey'        => 'preferred',
                    'requireResidentKey' => false,
                    'userVerification'   => 'required',
                ],
                'excludeCredentials'     => $excludeCredentials,
            ],
        ]);
    }

    public function registerVerify(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Anda harus login sebelum mengaktifkan sidik jari.'], 401);
        }

        $challenge = Cache::pull($this->registerCacheKey($user->id));

        if (! $challenge) {
            return response()->json(['message' => 'Sesi pendaftaran kedaluwarsa. Coba ulangi.'], 422);
        }

        try {
            $credential = $this->credentialPayload($request);

            $result = $this->verifier($request)->verifyRegistration(
                Base64Url::decodeLoose($credential['response']['clientDataJSON'] ?? ''),
                Base64Url::decodeLoose($credential['response']['attestationObject'] ?? ''),
                $challenge,
            );
        } catch (WebAuthnException $e) {
            return response()->json(['message' => 'Pendaftaran gagal: '.$e->getMessage()], 422);
        }

        $credentialId = Base64Url::encode($result['credential_id']);

        if (Passkey::where('credential_id', $credentialId)->exists()) {
            return response()->json(['message' => 'Perangkat ini sudah terdaftar sebelumnya.'], 422);
        }

        $passkey = Passkey::create([
            'user_id'       => $user->id,
            'credential_id' => $credentialId,
            'public_key'    => Base64Url::encode($result['public_key']),
            'counter'       => $result['sign_count'],
            'device_name'   => $request->input('device_name') ?: $this->guessDeviceName($request),
            'last_used_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Sidik jari / Face ID berhasil diaktifkan.',
            'passkey' => $this->passkeyPayload($passkey),
            'user'    => $this->userPayload($user),
        ], 201);
    }
    /* ───────────────────────── Login biometrik (public) ───────────────────────── */

    public function loginOptions(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $user     = null;

        if (is_string($username) && trim($username) !== '') {
            $user = User::where('username', $username)->orWhere('email', $username)->first();

            if (! $user) {
                return response()->json(['message' => 'Akun tidak ditemukan.'], 422);
            }
        }

        $challenge  = $this->newChallenge();
        $ceremonyId = (string) Str::uuid();

        Cache::put(
            $this->loginCacheKey($ceremonyId),
            ['challenge' => $challenge, 'user_id' => $user?->id],
            now()->addMinutes(self::CHALLENGE_TTL_MINUTES)
        );

        $allowCredentials = $user
            ? Passkey::where('user_id', $user->id)
                ->pluck('credential_id')
                ->map(fn (string $id) => ['type' => 'public-key', 'id' => $id])
                ->values()
                ->all()
            : [];

        return response()->json([
            'ceremony_id' => $ceremonyId,
            'options'     => [
                'challenge'        => $challenge,
                'rpId'             => $this->rpId($request),
                'timeout'          => 60000,
                'userVerification' => 'required',
                'allowCredentials' => $allowCredentials,
            ],
        ]);
    }

    public function loginVerify(Request $request): JsonResponse
    {
        $ceremonyId = $request->input('ceremony_id');

        if (! is_string($ceremonyId) || $ceremonyId === '') {
            return response()->json(['message' => 'Sesi login tidak dikenali. Coba lagi.'], 422);
        }

        $ceremony = Cache::pull($this->loginCacheKey($ceremonyId));

        if (! $ceremony) {
            return response()->json(['message' => 'Sesi login kedaluwarsa. Coba lagi.'], 422);
        }

        try {
            $credential   = $this->credentialPayload($request, requireAssertion: true);
            $credentialId = $this->normalizeCredentialId($credential);
            $clientData   = Base64Url::decodeLoose($credential['response']['clientDataJSON'] ?? '');
            $authData     = Base64Url::decodeLoose($credential['response']['authenticatorData'] ?? '');
            $signature    = Base64Url::decodeLoose($credential['response']['signature'] ?? '');
            $userHandle   = $credential['response']['userHandle'] ?? null;
        } catch (WebAuthnException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $passkey = Passkey::with('user')->where('credential_id', $credentialId)->first();

        if (! $passkey) {
            return response()->json([
                'message' => 'Perangkat ini belum terdaftar. Masuk dengan password lalu aktifkan sidik jari.',
            ], 404);
        }

        if (! empty($ceremony['user_id']) && (int) $passkey->user_id !== (int) $ceremony['user_id']) {
            return response()->json(['message' => 'Perangkat tidak cocok dengan akun yang diminta.'], 401);
        }

        $user = $passkey->user;

        if (! $user) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        if (in_array($user->status, ['pending', 'rejected'], true)) {
            return response()->json(['message' => 'Akun Anda belum aktif. Hubungi admin koperasi.'], 403);
        }

        if (is_string($userHandle) && $userHandle !== '') {
            try {
                if (Base64Url::decodeLoose($userHandle) !== (string) $user->id) {
                    return response()->json(['message' => 'Kredensial tidak cocok dengan akun.'], 401);
                }
            } catch (WebAuthnException $e) {
                return response()->json(['message' => 'User handle tidak valid.'], 422);
            }
        }

        try {
            $signCount = $this->verifier($request)->verifyAssertion(
                $clientData,
                $authData,
                $signature,
                Base64Url::decodeLoose($passkey->public_key),
                (int) $passkey->counter,
                $ceremony['challenge'],
            );
        } catch (WebAuthnException $e) {
            return response()->json(['message' => 'Login biometrik gagal: '.$e->getMessage()], 401);
        }

        $passkey->update(['counter' => $signCount, 'last_used_at' => now()]);

        $user->tokens()->delete();
        $token = $user->createToken('koperasi-passkey-token', [$user->role])->plainTextToken;

        return response()->json([
            'message'    => 'Login biometrik berhasil.',
            'token'      => $token,
            'token_type' => 'Bearer',
            'passkey'    => $this->passkeyPayload($passkey->fresh()),
            'user'       => $this->userPayload($user),
        ]);
    }

    /* ───────────────────────── Kelola perangkat ───────────────────────── */

    public function index(Request $request): JsonResponse
    {
        $passkeys = Passkey::where('user_id', $request->user()->id)
            ->orderByDesc('last_used_at')
            ->get()
            ->map(fn (Passkey $passkey) => $this->passkeyPayload($passkey));

        return response()->json(['data' => $passkeys]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $passkey = Passkey::where('user_id', $request->user()->id)->find($id);

        if (! $passkey) {
            return response()->json(['message' => 'Perangkat tidak ditemukan.'], 404);
        }

        $passkey->delete();

        return response()->json(['message' => 'Perangkat berhasil dihapus.']);
    }

    /* ───────────────────────── Helper ───────────────────────── */

    private function verifier(Request $request): WebAuthnVerifier
    {
        return new WebAuthnVerifier(
            $this->rpId($request),
            $this->origins($request),
            (bool) config('services.webauthn.require_user_verification', true),
        );
    }

    /**
     * Relying Party ID = host frontend. Diambil dari konfigurasi bila di-set,
     * kalau tidak dari header Origin (browser selalu mengirimkannya), lalu
     * fallback ke host request.
     */
    private function rpId(Request $request): string
    {
        $configured = config('services.webauthn.rp_id');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $origin = $this->frontendOrigin($request);

        if ($origin !== null) {
            $host = parse_url($origin, PHP_URL_HOST);

            if (is_string($host) && $host !== '') {
                return $host;
            }
        }

        return (string) $request->getHost();
    }

    /** @return array<string> */
    private function origins(Request $request): array
    {
        $configured = config('services.webauthn.origins', []);

        if (is_array($configured) && $configured !== []) {
            return array_values($configured);
        }

        $origin = $this->frontendOrigin($request);

        return [$origin ?? $request->getSchemeAndHttpHost()];
    }

    private function frontendOrigin(Request $request): ?string
    {
        $origin = (string) $request->headers->get('Origin', '');

        if ($origin === '' || $origin === 'null') {
            return null;
        }

        return rtrim($origin, '/');
    }

    private function newChallenge(): string
    {
        return Base64Url::encode(random_bytes(32));
    }

    private function registerCacheKey(int $userId): string
    {
        return "passkey:register:{$userId}";
    }

    private function loginCacheKey(string $ceremonyId): string
    {
        // Batasi karakter agar aman dipakai sebagai key cache
        return 'passkey:login:'.preg_replace('/[^A-Za-z0-9\-]/', '', $ceremonyId);
    }

    /**
     * @return array<string, mixed>
     */
    private function credentialPayload(Request $request, bool $requireAssertion = false): array
    {
        $credential = $request->input('credential');

        if (! is_array($credential) || ! is_array($credential['response'] ?? null)) {
            throw new WebAuthnException('Data kredensial tidak lengkap.');
        }

        $required = $requireAssertion
            ? ['clientDataJSON', 'authenticatorData', 'signature']
            : ['clientDataJSON', 'attestationObject'];

        foreach ($required as $field) {
            if (empty($credential['response'][$field])) {
                throw new WebAuthnException("Field {$field} tidak boleh kosong.");
            }
        }

        return $credential;
    }

    /**
     * @param  array<string, mixed> $credential
     */
    private function normalizeCredentialId(array $credential): string
    {
        $rawId = $credential['rawId'] ?? null;

        if (is_string($rawId) && $rawId !== '') {
            return Base64Url::encode(Base64Url::decodeLoose($rawId));
        }

        $id = $credential['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new WebAuthnException('Credential ID tidak dikirim.');
        }

        return Base64Url::encode(Base64Url::decodeLoose($id));
    }

    /**
     * @return array<string, mixed>
     */
    private function passkeyPayload(Passkey $passkey): array
    {
        return [
            'id'           => $passkey->id,
            'device_name'  => $passkey->device_name,
            'last_used_at' => optional($passkey->last_used_at)->toIso8601String(),
            'created_at'   => optional($passkey->created_at)->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id'       => $user->id,
            'nama'     => $user->nama_lengkap,
            'username' => $user->username,
            'email'    => $user->email,
            'avatar'   => $user->avatar,
            'role'     => $user->role,
            'status'   => $user->status,
        ];
    }

    /** Nama perangkat kasar dari User-Agent supaya mudah dikenali di daftar. */
    private function guessDeviceName(Request $request): string
    {
        $agent = (string) $request->userAgent();

        $platform = match (true) {
            str_contains($agent, 'iPhone')  => 'iPhone',
            str_contains($agent, 'iPad')    => 'iPad',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'Mac')     => 'macOS',
            str_contains($agent, 'Windows') => 'Windows',
            default                         => 'Perangkat',
        };

        return $platform.' · '.now()->format('d/m/Y');
    }
}


