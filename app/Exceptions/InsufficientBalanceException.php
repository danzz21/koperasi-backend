<?php

namespace App\Exceptions;

use RuntimeException;

/** Saldo E-Wallet tidak cukup untuk membayar transaksi. */
class InsufficientBalanceException extends RuntimeException
{
    public function __construct(
        public readonly float $saldo,
        public readonly float $dibutuhkan,
        string $message = 'Saldo E-Wallet tidak cukup untuk transaksi ini.',
    ) {
        parent::__construct($message);
    }
}
