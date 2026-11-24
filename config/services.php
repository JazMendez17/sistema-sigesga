<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        // Llave del SERVIDOR (nunca exponer en el frontend):
        // usada por el backend para llamar a Google Routes API v2 (rutas, peajes).
        'api_key' => env('GOOGLE_MAPS_API_KEY'),

        // Llave del NAVEGADOR (restringida por HTTP referrer):
        // usada por el frontend para Places Autocomplete y Maps JavaScript API.
        // No hay fallback a la llave de servidor: nunca debe exponerse en HTML.
        'frontend_key' => env('GOOGLE_MAPS_FRONTEND_KEY'),

        // Región y lenguaje para rutas, autocomplete y cantidades de peaje.
        'country' => env('GOOGLE_MAPS_COUNTRY', 'MX'),
        'language' => env('GOOGLE_MAPS_LANGUAGE', 'es'),
        'vehicle_type' => env('GOOGLE_MAPS_VEHICLE_TYPE', 'TRUCK'),
        'emission_type' => env('GOOGLE_MAPS_EMISSION_TYPE', 'GASOLINE'),

    ],

    'postalia' => [
        'api_key' => env('POSTALIA_API_KEY'),
        'base_url' => env('POSTALIA_BASE_URL', 'https://postali.app/api/v1'),
    ],

];
