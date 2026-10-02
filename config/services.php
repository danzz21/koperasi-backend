<?php

return [
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions'),
        'snap_js' => env('MIDTRANS_SNAP_JS', 'https://app.sandbox.midtrans.com/snap/snap.js'),
        // Endpoint RESTful untuk membuat charge QRIS / Virtual Account
        'api_url' => env(
            'MIDTRANS_API_URL',
            'https://api.sandbox.midtrans.com/v2'
        ),
        // Master switch integrasi topup e-wallet via Midtrans
        'enabled' => env('MIDTRANS_ENABLED', true),
        // Rekening koperasi untuk topup manual (diverifikasi admin)
        'bank_manual' => [
            'bank'    => env('KOPERASI_BANK', 'BCA'),
            'nomor'   => env('KOPERASI_REKENING', '0000000000'),
            'atas'    => env('KOPERASI_REKENING_ATAS', 'Koperasi K-Samara'),
        ],
    ],

    'digiflazz' => [
        'username' => env('DIGIFLAZZ_USERNAME'),
        'api_key' => env('DIGIFLAZZ_API_KEY'),
        'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
        'is_production' => env('DIGIFLAZZ_IS_PRODUCTION', false),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:5173'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WebAuthn / Passkey
    |--------------------------------------------------------------------------
    | rp_id  : host frontend (tanpa skema & port). Kosongkan agar otomatis
    |          diambil dari header Origin, mis. "localhost" atau "app.domain.com".
    | origins: daftar origin yang diizinkan, pisahkan dengan koma.
    */
    /*
    |--------------------------------------------------------------------------
    | Topup E-Wallet
    |--------------------------------------------------------------------------
    | auto_approve_manual:
    |   true  → unggah bukti transfer langsung menambah saldo (cocok kalau ada
    |           kasir/kas cooperative yang sudah menerima uang, atau bank
    |           mengirim mutasi otomatis ke sistem).
    |   false → bukti hanya disimpan, saldo menunggu verifikasi admin
    |           (default, lebih aman untuk transfer yang masuk lewat bank
    |           tanpa notifikasi otomatis).
    */
    'topup' => [
        'auto_approve_manual' => env('TOPUP_AUTO_APPROVE_MANUAL', false),
    ],

    'webauthn' => [
        'rp_id'   => env('WEBAUTHN_RP_ID'),
        'rp_name' => env('WEBAUTHN_RP_NAME', 'K-Samara'),
        'origins' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('WEBAUTHN_ORIGINS', ''))
        ))),
        'require_user_verification' => env('WEBAUTHN_REQUIRE_UV', true),
    ],
];