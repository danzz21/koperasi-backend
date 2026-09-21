<?php

namespace App\Support\WebAuthn;

/**
 * Helper encoding DER (ASN.1) secukupnya untuk membangun SubjectPublicKeyInfo
 * dari kunci COSE sebelum diserahkan ke OpenSSL.
 */
class Der
{
    public static function sequence(string ...$parts): string
    {
        $payload = implode('', $parts);

        return "\x30".self::length(strlen($payload)).$payload;
    }

    public static function objectIdentifier(string $oidBytes): string
    {
        return "\x06".self::length(strlen($oidBytes)).$oidBytes;
    }

    public static function bitString(string $bytes): string
    {
        // 0x00 di awal = jumlah bit yang tidak terpakai
        $payload = "\x00".$bytes;

        return "\x03".self::length(strlen($payload)).$payload;
    }

    public static function octetString(string $bytes): string
    {
        return "\x04".self::length(strlen($bytes)).$bytes;
    }

    public static function null(): string
    {
        return "\x05\x00";
    }

    /**
     * INTEGER unsigned (big-endian). Menambahkan 0x00 di depan bila bit
     * tertinggi menyala agar tetap dianggap bilangan positif.
     */
    public static function unsignedInteger(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");

        if ($bytes === '') {
            $bytes = "\x00";
        }

        if ((ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00".$bytes;
        }

        return "\x02".self::length(strlen($bytes)).$bytes;
    }

    private static function length(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $bytes = '';

        while ($length > 0) {
            $bytes = chr($length & 0xFF).$bytes;
            $length >>= 8;
        }

        return chr(0x80 | strlen($bytes)).$bytes;
    }
}
