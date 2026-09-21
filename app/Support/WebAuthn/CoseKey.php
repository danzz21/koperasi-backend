<?php

namespace App\Support\WebAuthn;

use RuntimeException;

/**
 * COSE_Key (RFC 8152) → kunci publik PEM, lalu verifikasi tanda tangan
 * memakai ekstensi OpenSSL bawaan PHP.
 *
 * Algoritma yang didukung:
 *   -7  ES256 (ECDSA P-256 / SHA-256)
 *   -35 ES384 (ECDSA P-384 / SHA-384)
 *   -36 ES512 (ECDSA P-521 / SHA-512)
 *   -257 RS256, -258 RS384, -259 RS512 (RSA PKCS#1 v1.5)
 *
 * Algoritma EdDSA (-8) tidak didukung karena butuh libsodium/OpenSSL khusus.
 */
class CoseKey
{
    /** Pemetaan alg COSE → konstanta OpenSSL. */
    private const OPENSSL_ALGORITHMS = [
        -7   => OPENSSL_ALGO_SHA256,
        -35  => OPENSSL_ALGO_SHA384,
        -36  => OPENSSL_ALGO_SHA512,
        -257 => OPENSSL_ALGO_SHA256,
        -258 => OPENSSL_ALGO_SHA384,
        -259 => OPENSSL_ALGO_SHA512,
    ];

    /** Pemetaan COSE crv → DER OID kurva. */
    private const CURVE_OIDS = [
        1 => "\x2A\x86\x48\xCE\x3D\x03\x01\x07", // P-256 (prime256v1)
        2 => "\x2B\x81\x04\x00\x22",             // P-384 (secp384r1)
        3 => "\x2B\x81\x04\x00\x23",             // P-521 (secp521r1)
    ];

    private const OID_EC_PUBLIC_KEY = "\x2A\x86\x48\xCE\x3D\x02\x01";   // 1.2.840.10045.2.1
    private const OID_RSA_ENCRYPTION = "\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01"; // 1.2.840.113549.1.1.1

    /** @param array<int|string, mixed> $coseKey hasil decode CBOR */
    public function __construct(private readonly array $coseKey)
    {
    }

    public static function fromCbor(string $cborBytes): self
    {
        $decoded = Cbor::decode($cborBytes);

        if (! is_array($decoded)) {
            throw new RuntimeException('Public key COSE tidak valid.');
        }

        return new self($decoded);
    }

    public function algorithm(): int
    {
        return (int) ($this->coseKey[3] ?? 0);
    }

    public function supportsVerification(): bool
    {
        return array_key_exists($this->algorithm(), self::OPENSSL_ALGORITHMS);
    }

    public function toPem(): string
    {
        $alg = $this->algorithm();

        if (! array_key_exists($alg, self::OPENSSL_ALGORITHMS)) {
            throw new RuntimeException("Algoritma COSE {$alg} belum didukung untuk verifikasi.");
        }

        $der = in_array($alg, [-7, -35, -36], true)
            ? $this->ecDer()
            : $this->rsaDer();

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($der), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    /**
     * Verifikasi tanda tangan WebAuthn atas $data.
     */
    public function verify(string $signature, string $data): bool
    {
        $key = openssl_pkey_get_public($this->toPem());

        if ($key === false) {
            throw new RuntimeException('Gagal memuat public key dari COSE.');
        }

        $result = openssl_verify($data, $signature, $key, self::OPENSSL_ALGORITHMS[$this->algorithm()]);

        if ($result === -1) {
            throw new RuntimeException('Verifikasi tanda tangan gagal dijalankan: '.openssl_error_string());
        }

        return $result === 1;
    }

    private function ecDer(): string
    {
        $curve = (int) ($this->coseKey[-1] ?? 0);

        if (! isset(self::CURVE_OIDS[$curve])) {
            throw new RuntimeException('Kurva COSE tidak dikenal.');
        }

        $x = (string) ($this->coseKey[-2] ?? '');
        $y = (string) ($this->coseKey[-3] ?? '');

        if ($x === '' || $y === '') {
            throw new RuntimeException('Koordinat public key EC tidak lengkap.');
        }

        $algorithm = Der::sequence(
            Der::objectIdentifier(self::OID_EC_PUBLIC_KEY)
            .Der::objectIdentifier(self::CURVE_OIDS[$curve])
        );

        // ECPoint: 0x04 || X || Y, dibungkus BIT STRING
        return Der::sequence($algorithm.Der::bitString("\x04".$x.$y));
    }

    private function rsaDer(): string
    {
        $n = (string) ($this->coseKey[-1] ?? '');
        $e = (string) ($this->coseKey[-2] ?? '');

        if ($n === '' || $e === '') {
            throw new RuntimeException('Modulus / eksponen public key RSA tidak lengkap.');
        }

        $algorithm = Der::sequence(
            Der::objectIdentifier(self::OID_RSA_ENCRYPTION).Der::null()
        );

        $rsaKey = Der::sequence(Der::unsignedInteger($n).Der::unsignedInteger($e));

        return Der::sequence($algorithm.Der::bitString($rsaKey));
    }
}
