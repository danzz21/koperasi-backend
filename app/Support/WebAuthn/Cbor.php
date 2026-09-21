<?php

namespace App\Support\WebAuthn;

use RuntimeException;

/**
 * Decoder CBOR minimalis (RFC 8949) — hanya bagian yang dibutuhkan WebAuthn:
 * attestationObject, COSE_Key (public key), dan struktur map/array dasar.
 *
 * Tidak mendukung floating point & tag (tag dilewati, isinya dikembalikan).
 */
class Cbor
{
    private int $offset = 0;

    private int $length;

    private function __construct(private readonly string $data)
    {
        $this->length = strlen($data);
    }

    public static function decode(string $data): mixed
    {
        return (new self($data))->read();
    }

    /**
     * Decode satu nilai lalu kembalikan sisa byte-nya.
     * Dipakai untuk membaca COSE_Key yang diikuti extension data.
     *
     * @return array{0: mixed, 1: string}
     */
    public static function decodeWithRemainder(string $data): array
    {
        $decoder = new self($data);
        $value   = $decoder->read();

        return [$value, substr($data, $decoder->offset)];
    }

    private function read(): mixed
    {
        $initial    = $this->readByte();
        $majorType  = $initial >> 5;
        $additional = $initial & 0x1F;

        return match ($majorType) {
            0 => $this->readLength($additional),                 // unsigned integer
            1 => -1 - $this->readLength($additional),            // negative integer
            2 => $this->readByteString($additional),             // byte string
            3 => $this->readTextString($additional),             // text string
            4 => $this->readArray($additional),                  // array
            5 => $this->readMap($additional),                    // map
            6 => $this->readTagged($additional),                 // tagged value
            7 => $this->readSimple($additional),                 // simple / float
            default => throw new RuntimeException('CBOR: major type tidak dikenal.'),
        };
    }

    private function readByte(): int
    {
        if ($this->offset >= $this->length) {
            throw new RuntimeException('CBOR: data berakhir lebih cepat dari perkiraan.');
        }

        return ord($this->data[$this->offset++]);
    }

    private function readRaw(int $bytes): string
    {
        if ($bytes < 0 || $this->offset + $bytes > $this->length) {
            throw new RuntimeException('CBOR: panjang data tidak valid.');
        }

        $chunk = substr($this->data, $this->offset, $bytes);
        $this->offset += $bytes;

        return $chunk;
    }

    private function readLength(int $additional): int
    {
        return match ($additional) {
            24 => $this->readByte(),
            25 => unpack('n', $this->readRaw(2))[1],
            26 => unpack('N', $this->readRaw(4))[1],
            27 => $this->readUint64($this->readRaw(8)),
            default => $additional, // 0..23 langsung; 28..31 bukan panjang definit
        };
    }

    private function readUint64(string $bytes): int
    {
        $parts = unpack('N2', $bytes);

        return ($parts[1] << 32) | $parts[2];
    }

    private function readByteString(int $additional): string
    {
        if ($additional === 31) {
            $buffer = '';
            while (! $this->isBreak()) {
                $buffer .= $this->read();
            }
            $this->readByte();

            return $buffer;
        }

        return $this->readRaw($this->readLength($additional));
    }

    private function readTextString(int $additional): string
    {
        if ($additional === 31) {
            $buffer = '';
            while (! $this->isBreak()) {
                $buffer .= $this->read();
            }
            $this->readByte();

            return $buffer;
        }

        return $this->readRaw($this->readLength($additional));
    }

    private function readArray(int $additional): array
    {
        $items = [];

        if ($additional === 31) {
            while (! $this->isBreak()) {
                $items[] = $this->read();
            }
            $this->readByte();

            return $items;
        }

        for ($i = 0, $count = $this->readLength($additional); $i < $count; $i++) {
            $items[] = $this->read();
        }

        return $items;
    }

    private function readMap(int $additional): array
    {
        $map = [];

        if ($additional === 31) {
            while (! $this->isBreak()) {
                $key        = $this->read();
                $map[$key]  = $this->read();
            }
            $this->readByte();

            return $map;
        }

        for ($i = 0, $count = $this->readLength($additional); $i < $count; $i++) {
            $key       = $this->read();
            $map[$key] = $this->read();
        }

        return $map;
    }

    private function readTagged(int $additional): mixed
    {
        if ($additional !== 31) {
            $this->readLength($additional); // nomor tag dibuang
        }

        return $this->read();
    }

    private function readSimple(int $additional): mixed
    {
        return match ($additional) {
            20 => false,
            21 => true,
            22, 23 => null,
            24 => $this->readByte(),
            25 => $this->readRaw(2),
            26 => $this->readRaw(4),
            27 => $this->readRaw(8),
            default => null,
        };
    }

    private function isBreak(): bool
    {
        return $this->offset < $this->length && ord($this->data[$this->offset]) === 0xFF;
    }
}
