<?php

namespace App\Support\WebAuthn;

use RuntimeException;

/**
 * Helper encoding base64url (RFC 4648 §5) yang dipakai WebAuthn.
 *
 * Semua data biner pada WebAuthn (challenge, credential id, signature, dll)
 * dikirim sebagai base64url tanpa padding.
 */
class Base64Url
{
    public static function encode(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }

    public static function decode(string $value): string
    {
        $normalized = strtr($value, '-_', '+/');
        $padding    = strlen($normalized) % 4;

        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);

        if ($decoded === false) {
            throw new RuntimeException('Encoding base64url tidak valid.');
        }

        return $decoded;
    }

    /**
     * Decode nilai yang bisa datang sebagai base64url (frontend) atau
     * base64 standar / biner mentah (mis. dari curl atau testing manual).
     */
    public static function decodeLoose(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            throw new RuntimeException('Data biner wajib diisi.');
        }

        // Biner mentah dengan byte non-printable → pakai apa adanya
        if (preg_match('/[^\x20-\x7E]/', $value) === 1) {
            return $value;
        }

        return self::decode($value);
    }
}
