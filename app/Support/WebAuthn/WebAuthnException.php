<?php

namespace App\Support\WebAuthn;

use RuntimeException;

/**
 * Dilempar saat data dari authenticator tidak lolos verifikasi.
 * Pesannya sengaja aman untuk ditampilkan ke pengguna.
 */
class WebAuthnException extends RuntimeException
{
}
