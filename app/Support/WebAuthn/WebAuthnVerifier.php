<?php

namespace App\Support\WebAuthn;

/**
 * Verifikasi server-side WebAuthn Level 2 (registrasi & assertion).
 *
 * Dipakai oleh PasskeyAuthController. Tidak memerlukan package eksternal:
 * parsing CBOR + verifikasi tanda tangan memakai OpenSSL bawaan PHP.
 */
class WebAuthnVerifier
{
    private const FLAG_UP = 0x01; // User Present
    private const FLAG_UV = 0x04; // User Verified
    private const FLAG_AT = 0x40; // Attested credential data included
    private const FLAG_ED = 0x80; // Extension data included

    private const MAX_CREDENTIAL_ID_LENGTH = 1023;
    private const AUTH_DATA_MIN_LENGTH     = 37;

    /**
     * @param  string        $rpId                      Relying Party ID (host tanpa port)
     * @param  array<string> $allowedOrigins            Origin yang diizinkan
     * @param  bool          $requireUserVerification   Wajib biometrik/PIN (bukan sekadar tap)
     */
    public function __construct(
        private readonly string $rpId,
        private readonly array $allowedOrigins = [],
        private readonly bool $requireUserVerification = true,
    ) {
    }

    public function rpId(): string
    {
        return $this->rpId;
    }

    /**
     * Verifikasi clientDataJSON sesuai tipe ceremony (create/get).
     *
     * @return array<string, mixed>
     */
    public function verifyClientData(
        string $rawClientDataJson,
        string $expectedType,
        string $expectedChallenge,
    ): array {
        $data = json_decode($rawClientDataJson, true);

        if (! is_array($data)) {
            throw new WebAuthnException('clientDataJSON tidak dapat dibaca.');
        }

        if (($data['type'] ?? null) !== $expectedType) {
            throw new WebAuthnException('Jenis operasi WebAuthn tidak sesuai.');
        }

        $challenge = (string) ($data['challenge'] ?? '');

        if ($challenge === '' || ! hash_equals($expectedChallenge, $challenge)) {
            throw new WebAuthnException('Challenge tidak cocok atau sudah kedaluwarsa.');
        }

        $origin = (string) ($data['origin'] ?? '');

        if ($this->allowedOrigins !== [] && ! in_array($origin, $this->allowedOrigins, true)) {
            throw new WebAuthnException('Origin tidak diizinkan: '.$origin);
        }

        return $data;
    }

    /**
     * Urai authenticatorData.
     *
     * @return array{
     *     rp_id_hash: string, flags: int, sign_count: int, aaguid: ?string,
     *     credential_id: ?string, public_key: ?string, extensions: mixed
     * }
     */
    public function parseAuthenticatorData(string $authData): array
    {
        if (strlen($authData) < self::AUTH_DATA_MIN_LENGTH) {
            throw new WebAuthnException('Authenticator data terlalu pendek.');
        }

        $rpIdHash  = substr($authData, 0, 32);
        $flags     = ord($authData[32]);
        $signCount = unpack('N', substr($authData, 33, 4))[1];

        if (! hash_equals(hash('sha256', $this->rpId, true), $rpIdHash)) {
            throw new WebAuthnException('Domain aplikasi tidak cocok dengan kredensial.');
        }

        $result = [
            'rp_id_hash'    => $rpIdHash,
            'flags'         => $flags,
            'sign_count'    => $signCount,
            'aaguid'        => null,
            'credential_id' => null,
            'public_key'    => null,
            'extensions'    => null,
        ];

        $offset = self::AUTH_DATA_MIN_LENGTH;

        if (($flags & self::FLAG_AT) !== 0) {
            if (strlen($authData) < $offset + 18) {
                throw new WebAuthnException('Attested credential data tidak lengkap.');
            }

            $result['aaguid'] = substr($authData, $offset, 16);
            $offset += 16;

            $credentialIdLength = unpack('n', substr($authData, $offset, 2))[1];
            $offset += 2;

            if ($credentialIdLength === 0 || $credentialIdLength > self::MAX_CREDENTIAL_ID_LENGTH) {
                throw new WebAuthnException('Panjang credential ID tidak wajar.');
            }

            if (strlen($authData) < $offset + $credentialIdLength) {
                throw new WebAuthnException('Credential ID terpotong.');
            }

            $result['credential_id'] = substr($authData, $offset, $credentialIdLength);
            $offset += $credentialIdLength;

            [$coseKey, $remainder] = Cbor::decodeWithRemainder(substr($authData, $offset));

            if (! is_array($coseKey)) {
                throw new WebAuthnException('Public key COSE tidak dapat dibaca.');
            }

            $consumed            = strlen($authData) - $offset - strlen($remainder);
            $result['public_key'] = substr($authData, $offset, $consumed);
            $offset += $consumed;
        }

        if (($flags & self::FLAG_ED) !== 0 && strlen($authData) > $offset) {
            $result['extensions'] = Cbor::decode(substr($authData, $offset));
        }

        return $result;
    }

    /**
     * Verifikasi registrasi passkey baru.
     *
     * @return array{credential_id: string, public_key: string, sign_count: int, aaguid: string, alg: int}
     */
    public function verifyRegistration(
        string $clientDataJson,
        string $attestationObject,
        string $expectedChallenge,
    ): array {
        $attestation = Cbor::decode($attestationObject);

        if (! is_array($attestation) || ! isset($attestation['authData'])) {
            throw new WebAuthnException('Attestation object tidak valid.');
        }

        $authData = $this->parseAuthenticatorData((string) $attestation['authData']);

        $this->verifyClientData($clientDataJson, 'webauthn.create', $expectedChallenge);

        if (($authData['flags'] & self::FLAG_UP) === 0) {
            throw new WebAuthnException('Autentikator tidak mengonfirmasi kehadiran pengguna.');
        }

        if ($this->requireUserVerification && ($authData['flags'] & self::FLAG_UV) === 0) {
            throw new WebAuthnException('Verifikasi biometrik/PIN tidak dilakukan.');
        }

        if ($authData['credential_id'] === null || $authData['public_key'] === null) {
            throw new WebAuthnException('Kredensial tidak berisi public key.');
        }

        $this->verifyAttestationStatement($attestation, $authData, $clientDataJson);

        return [
            'credential_id' => $authData['credential_id'],
            'public_key'    => $authData['public_key'],
            'sign_count'    => $authData['sign_count'],
            'aaguid'        => bin2hex($authData['aaguid']),
            'alg'           => CoseKey::fromCbor($authData['public_key'])->algorithm(),
        ];
    }

    /**
     * Verifikasi assertion (login) memakai public key yang tersimpan.
     * Mengembalikan sign count terbaru untuk disimpan.
     */
    public function verifyAssertion(
        string $clientDataJson,
        string $authenticatorData,
        string $signature,
        string $storedPublicKeyCbor,
        int $storedSignCount,
        string $expectedChallenge,
    ): int {
        $authData = $this->parseAuthenticatorData($authenticatorData);

        $this->verifyClientData($clientDataJson, 'webauthn.get', $expectedChallenge);

        if (($authData['flags'] & self::FLAG_UP) === 0) {
            throw new WebAuthnException('Autentikator tidak mengonfirmasi kehadiran pengguna.');
        }

        if ($this->requireUserVerification && ($authData['flags'] & self::FLAG_UV) === 0) {
            throw new WebAuthnException('Verifikasi biometrik/PIN tidak dilakukan.');
        }

        $signedData = $authenticatorData.hash('sha256', $clientDataJson, true);
        $publicKey  = CoseKey::fromCbor($storedPublicKeyCbor);

        if (! $publicKey->verify($signature, $signedData)) {
            throw new WebAuthnException('Tanda tangan passkey tidak valid.');
        }

        $signCount = (int) $authData['sign_count'];

        // Deteksi kemungkinan kunci diduplikasi: counter harus naik,
        // kecuali perangkat tidak menghitungnya (nilai 0).
        if ($storedSignCount > 0 && $signCount > 0 && $signCount <= $storedSignCount) {
            throw new WebAuthnException('Penghitung autentikator tidak wajar. Daftarkan ulang perangkat ini.');
        }

        return $signCount;
    }

    /**
     * Attestation tidak diwajibkan (attestation: "none"), tapi bila
     * autentikator mengirim statement "packed" self-attestation kita
     * verifikasi tanda tangannya sebagai lapisan tambahan.
     *
     * @param  array<string, mixed> $attestation
     * @param  array<string, mixed> $authData
     */
    private function verifyAttestationStatement(array $attestation, array $authData, string $clientDataJson): void
    {
        $format = (string) ($attestation['fmt'] ?? '');
        $statement = $attestation['attStmt'] ?? [];

        if ($format !== 'packed' || ! is_array($statement) || isset($statement['x5c'])) {
            // none / self / apple / android-key / tpm / packed dengan sertifikat
            // → tidak diverifikasi (attestation: none pada opsi registrasi).
            return;
        }

        $signature = $statement['sig'] ?? null;

        if (! is_string($signature) || $signature === '') {
            return;
        }

        $verificationData = $attestation['authData'].hash('sha256', $clientDataJson, true);
        $publicKey        = CoseKey::fromCbor($authData['public_key']);

        if (! $publicKey->verify($signature, $verificationData)) {
            throw new WebAuthnException('Attestation statement tidak valid.');
        }
    }
}
