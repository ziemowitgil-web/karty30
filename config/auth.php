<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Defaultowy "guard" i broker resetowania hasła.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Guardy używane przez aplikację.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Tutaj definiujemy sposób pobierania użytkowników z bazy.
    | Zmieniliśmy driver na 'eloquent-webauthn' dla YubiKey/WebAuthn.
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent-webauthn',
            'model' => env('AUTH_MODEL', App\Models\User::class),
            'password_fallback' => true, // fallback na hasło, jeśli brak YubiKey
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetowanie haseł
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout potwierdzenia hasła
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
