<?php

return [
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions'),
        'snap_js' => env('MIDTRANS_SNAP_JS', 'https://app.sandbox.midtrans.com/snap/snap.js'),
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