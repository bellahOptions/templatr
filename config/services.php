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

    'admin' => [
        'notification_email' => env('ADMIN_NOTIFICATION_EMAIL'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudinary
    |--------------------------------------------------------------------------
    */
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | These must live in config (not be read with env() at runtime) so that
    | `php artisan config:cache` — which every shared host should run — does not
    | silently blank the gateways and break downloads.
    |
    */

    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'live' => env('PAYSTACK_LIVE_MODE', false),
        'split_code' => env('PAYSTACK_SPLIT_CODE'),
        'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL'),
    ],

    'flutterwave' => [
        'secret' => env('FLUTTERWAVE_SECRET_KEY'),
        'public' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'encryption' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'live' => env('FLUTTERWAVE_LIVE_MODE', false),
        'secret_hash' => env('FLW_SECRET_HASH'),
    ],

    'interswitch' => [
        'client_id' => env('INTERSWITCH_CLIENT_ID'),
        'client_secret' => env('INTERSWITCH_CLIENT_SECRET'),
        'merchant_code' => env('INTERSWITCH_MERCHANT_CODE'),
        'terminal_id' => env('INTERSWITCH_TERMINAL_ID'),
        'pay_item_id' => env('INTERSWITCH_PAY_ITEM_ID', '101'),
        'live' => env('INTERSWITCH_LIVE_MODE', false),
        'base_url' => env('INTERSWITCH_BASE_URL', 'https://sandbox.interswitch.co.ke'),
    ],

];
