<?php

namespace Tests\Unit;

use App\Support\WebAuthn\Base64Url;
use App\Support\WebAuthn\WebAuthnException;
use App\Support\WebAuthn\WebAuthnVerifier;
use PHPUnit\Framework\TestCase;

/**
 * Menguji verifier WebAuthn memakai kunci yang benar-benar dibuat OpenSSL,
 * sehingga jalur kripto (parsing CBOR/COSE + verifikasi tanda tangan)
 * terbukti bekerja tanpa perlu browser/authenticator fisik.
 */
class WebAuthnVerifierTest extends TestCase
{
    private const RP_ID     = 'localhost';
    private const ORIGIN    = 'http://localhost:5173';
    private const CHALLENGE = 'dGVzdC1jaGFsbGVuZ2UtMTIzNDU2Nzg5MA';

    private function verifier(string $rpId = self::RP_ID, array $origins = [self::ORIGIN]): WebAuthnVerifier
    {
        return new WebAuthnVerifier($rpId, $origins, true);
    }

    private function ecKey(): \OpenSSLAsymmetricKey
    {
        return $this->newKey([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ]);
    }

    private function rsaKey(): \OpenSSLAsymmetricKey
    {
        return $this->newKey([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
    }

    /**
     * Di Windows, OpenSSL perlu openssl.cnf untuk membuat kunci baru
     * (jalur verifikasi runtime tidak perlu). Cari lokasinya lalu pakai.
     */
    private function opensslConfig(): ?string
    {
        $fromEnv = getenv('OPENSSL_CONF');

        $candidates = array_filter([
            is_string($fromEnv) && $fromEnv !== '' ? $fromEnv : null,
            dirname(PHP_BINARY).'\\extras\\ssl\\openssl.cnf',
            dirname(PHP_BINARY).'\\openssl.cnf',
            'C:\\Program Files\\Common Files\\SSL\\openssl.cnf',
            '/etc/ssl/openssl.cnf',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed> $options
     */
    private function newKey(array $options): \OpenSSLAsymmetricKey
    {
        $config = $this->opensslConfig();

        if ($config !== null) {
            $options['config'] = $config;
        }

        $key = openssl_pkey_new($options);

        if (! $key instanceof \OpenSSLAsymmetricKey) {
            $this->markTestSkipped('OpenSSL tidak dapat membuat kunci: '.(string) openssl_error_string());
        }

        return $key;
    }

    public function test_base64url_round_trip(): void
    {
        $binary = random_bytes(32);

        $encoded = Base64Url::encode($binary);

        $this->assertStringNotContainsString('=', $encoded);
        $this->assertSame($binary, Base64Url::decode($encoded));
        $this->assertSame($binary, Base64Url::decodeLoose($encoded));
    }

    public function test_verifies_es256_registration(): void
    {
        $key   = $this->ecKey();
        $cose  = $this->coseEs256($key);
        $credId = random_bytes(32);

        $clientData = $this->clientData('webauthn.create', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->authData(self::RP_ID, 0x45, 0, $credId, $cose);

        $attestation = $this->attestationObject($authData, $clientData, $key);

        $result = $this->verifier()->verifyRegistration($clientData, $attestation, self::CHALLENGE);

        $this->assertSame($credId, $result['credential_id']);
        $this->assertSame($cose, $result['public_key']);
        $this->assertSame(-7, $result['alg']);
        $this->assertSame(0, $result['sign_count']);
    }

    public function test_verifies_es256_assertion_and_returns_new_counter(): void
    {
        $key    = $this->ecKey();
        $cose   = $this->coseEs256($key);
        $credId = random_bytes(32);

        $registration = $this->verifier()->verifyRegistration(
            $this->clientData('webauthn.create', self::CHALLENGE, self::ORIGIN),
            $this->attestationObject(
                $this->authData(self::RP_ID, 0x45, 0, $credId, $cose),
                $this->clientData('webauthn.create', self::CHALLENGE, self::ORIGIN),
                $key,
            ),
            self::CHALLENGE,
        );

        $clientData = $this->clientData('webauthn.get', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->assertionAuthData(self::RP_ID, 0x05, 7);
        $signature  = $this->sign($authData, $clientData, $key);

        $newCount = $this->verifier()->verifyAssertion(
            $clientData,
            $authData,
            $signature,
            $registration['public_key'],
            3,
            self::CHALLENGE,
        );

        $this->assertSame(7, $newCount);
    }

    public function test_rejects_wrong_challenge(): void
    {
        $key    = $this->ecKey();
        $cose   = $this->coseEs256($key);
        $client = $this->clientData('webauthn.create', 'challenge-palsu', self::ORIGIN);

        $this->expectException(WebAuthnException::class);

        $this->verifier()->verifyRegistration(
            $client,
            $this->attestationObject(
                $this->authData(self::RP_ID, 0x45, 0, random_bytes(32), $cose),
                $client,
                $key,
            ),
            self::CHALLENGE,
        );
    }

    public function test_rejects_wrong_origin(): void
    {
        $key    = $this->ecKey();
        $cose   = $this->coseEs256($key);
        $client = $this->clientData('webauthn.create', self::CHALLENGE, 'https://situs-jahat.example');

        $this->expectException(WebAuthnException::class);

        $this->verifier()->verifyRegistration(
            $client,
            $this->attestationObject(
                $this->authData(self::RP_ID, 0x45, 0, random_bytes(32), $cose),
                $client,
                $key,
            ),
            self::CHALLENGE,
        );
    }

    public function test_rejects_wrong_rp_id(): void
    {
        $key    = $this->ecKey();
        $cose   = $this->coseEs256($key);
        $client = $this->clientData('webauthn.create', self::CHALLENGE, self::ORIGIN);

        $this->expectException(WebAuthnException::class);

        // Kredensial diterbitkan untuk 'localhost', bukan 'koperasi.example'
        $this->verifier('koperasi.example')->verifyRegistration(
            $client,
            $this->attestationObject(
                $this->authData(self::RP_ID, 0x45, 0, random_bytes(32), $cose),
                $client,
                $key,
            ),
            self::CHALLENGE,
        );
    }

    public function test_rejects_tampered_signature(): void
    {
        $key        = $this->ecKey();
        $clientData = $this->clientData('webauthn.get', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->assertionAuthData(self::RP_ID, 0x05, 5);
        $signature  = $this->sign($authData, $clientData, $key);

        // Rusak satu byte tanda tangan
        $signature[10] = chr(ord($signature[10]) ^ 0xFF);

        $this->expectException(WebAuthnException::class);
        $this->expectExceptionMessage('Tanda tangan passkey tidak valid.');

        $this->verifier()->verifyAssertion(
            $clientData,
            $authData,
            $signature,
            $this->coseEs256($key),
            0,
            self::CHALLENGE,
        );
    }

    public function test_rejects_replayed_sign_counter(): void
    {
        $key        = $this->ecKey();
        $clientData = $this->clientData('webauthn.get', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->assertionAuthData(self::RP_ID, 0x05, 4);
        $signature  = $this->sign($authData, $clientData, $key);

        $this->expectException(WebAuthnException::class);

        // Counter tersimpan 4, assertion datang dengan 4 (tidak naik)
        $this->verifier()->verifyAssertion(
            $clientData,
            $authData,
            $signature,
            $this->coseEs256($key),
            4,
            self::CHALLENGE,
        );
    }

    public function test_rejects_missing_user_presence_flag(): void
    {
        $key        = $this->ecKey();
        $clientData = $this->clientData('webauthn.get', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->assertionAuthData(self::RP_ID, 0x04, 1); // UV saja, UP tidak diset
        $signature  = $this->sign($authData, $clientData, $key);

        $this->expectException(WebAuthnException::class);

        $this->verifier()->verifyAssertion(
            $clientData,
            $authData,
            $signature,
            $this->coseEs256($key),
            0,
            self::CHALLENGE,
        );
    }

    public function test_rejects_missing_user_verification_flag(): void
    {
        $key        = $this->ecKey();
        $clientData = $this->clientData('webauthn.get', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->assertionAuthData(self::RP_ID, 0x01, 1); // UP saja
        $signature  = $this->sign($authData, $clientData, $key);

        $this->expectException(WebAuthnException::class);

        $this->verifier()->verifyAssertion(
            $clientData,
            $authData,
            $signature,
            $this->coseEs256($key),
            0,
            self::CHALLENGE,
        );
    }

    public function test_verifies_rs256_registration_and_assertion(): void
    {
        $key   = $this->rsaKey();
        $cose  = $this->coseRs256($key);
        $credId = random_bytes(32);

        $clientData = $this->clientData('webauthn.create', self::CHALLENGE, self::ORIGIN);
        $authData   = $this->authData(self::RP_ID, 0x45, 0, $credId, $cose);

        $registration = $this->verifier()->verifyRegistration(
            $clientData,
            $this->attestationObject($authData, $clientData, $key, -257),
            self::CHALLENGE,
        );

        $this->assertSame(-257, $registration['alg']);

        $assertionClientData = $this->clientData('webauthn.get', self::CHALLENGE, self::ORIGIN);
        $assertionAuthData   = $this->assertionAuthData(self::RP_ID, 0x05, 2);

        $newCount = $this->verifier()->verifyAssertion(
            $assertionClientData,
            $assertionAuthData,
            $this->sign($assertionAuthData, $assertionClientData, $key),
            $cose,
            0,
            self::CHALLENGE,
        );

        $this->assertSame(2, $newCount);
    }

    /* ───────────── Helper pembentuk data WebAuthn ───────────── */

    private function sign(string $authenticatorData, string $clientDataJson, \OpenSSLAsymmetricKey $key): string
    {
        openssl_sign(
            $authenticatorData.hash('sha256', $clientDataJson, true),
            $signature,
            $key,
            OPENSSL_ALGO_SHA256,
        );

        return $signature;
    }

    private function clientData(string $type, string $challenge, string $origin): string
    {
        return json_encode([
            'type'        => $type,
            'challenge'   => $challenge,
            'origin'      => $origin,
            'crossOrigin' => false,
        ], JSON_UNESCAPED_SLASHES);
    }

    private function authData(string $rpId, int $flags, int $signCount, string $credentialId, string $coseKey): string
    {
        return hash('sha256', $rpId, true)
            .chr($flags)
            .pack('N', $signCount)
            .str_repeat("\x00", 16) // AAGUID
            .pack('n', strlen($credentialId))
            .$credentialId
            .$coseKey;
    }

    private function assertionAuthData(string $rpId, int $flags, int $signCount): string
    {
        return hash('sha256', $rpId, true).chr($flags).pack('N', $signCount);
    }

    private function attestationObject(
        string $authData,
        string $clientDataJson,
        \OpenSSLAsymmetricKey $key,
        int $alg = -7,
    ): string {
        openssl_sign($authData.hash('sha256', $clientDataJson, true), $sig, $key, OPENSSL_ALGO_SHA256);

        return $this->cborMap(3)
            .$this->cborText('fmt').$this->cborText('packed')
            .$this->cborText('attStmt')
            .$this->cborMap(2)
                .$this->cborText('alg').$this->cborInt($alg)
                .$this->cborText('sig').$this->cborBytes($sig)
            .$this->cborText('authData').$this->cborBytes($authData);
    }

    private function coseEs256(\OpenSSLAsymmetricKey $key): string
    {
        $ec = openssl_pkey_get_details($key)['ec'];

        return $this->cborMap(5)
            .$this->cborInt(1).$this->cborInt(2)   // kty = EC2
            .$this->cborInt(3).$this->cborInt(-7)  // alg = ES256
            .$this->cborInt(-1).$this->cborInt(1)  // crv = P-256
            .$this->cborInt(-2).$this->cborBytes($ec['x'])
            .$this->cborInt(-3).$this->cborBytes($ec['y']);
    }

    private function coseRs256(\OpenSSLAsymmetricKey $key): string
    {
        $rsa = openssl_pkey_get_details($key)['rsa'];

        return $this->cborMap(4)
            .$this->cborInt(1).$this->cborInt(3)    // kty = RSA
            .$this->cborInt(3).$this->cborInt(-257) // alg = RS256
            .$this->cborInt(-1).$this->cborBytes(ltrim($rsa['n'], "\x00"))
            .$this->cborInt(-2).$this->cborBytes(ltrim($rsa['e'], "\x00"));
    }

    /* ───────────── CBOR encoder minimalis (membangun fixture) ───────────── */

    private function cborMap(int $count): string
    {
        return $this->cborHead(5, $count);
    }

    private function cborInt(int $value): string
    {
        return $value >= 0
            ? $this->cborHead(0, $value)
            : $this->cborHead(1, -1 - $value);
    }

    private function cborBytes(string $bytes): string
    {
        return $this->cborHead(2, strlen($bytes)).$bytes;
    }

    private function cborText(string $text): string
    {
        return $this->cborHead(3, strlen($text)).$text;
    }

    private function cborHead(int $majorType, int $value): string
    {
        $prefix = $majorType << 5;

        return match (true) {
            $value < 24      => chr($prefix | $value),
            $value < 0x100   => chr($prefix | 24).chr($value),
            $value < 0x10000 => chr($prefix | 25).pack('n', $value),
            default          => chr($prefix | 26).pack('N', $value),
        };
    }
}
