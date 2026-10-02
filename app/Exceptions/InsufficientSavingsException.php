<?php

namespace App\Exceptions;

use RuntimeException;

/** Saldo simpanan anggota tidak cukup untuk dipindahkan ke E-Wallet. */
class InsufficientSavingsException extends RuntimeException
{
    public function __construct(
        public readonly float $tersedia,
        public readonly float $dibutuhkan,
        string $message = 'Saldo simpanan tidak cukup untuk dipindahkan ke E-Wallet.',
    ) {
        parent::__construct($message);
    }
}
