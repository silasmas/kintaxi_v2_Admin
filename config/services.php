<?php

return [

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

    'sms' => [
        'url' => trim((string) env('SMS_URL', '')),
        'balance_url' => trim((string) env('SMS_BALANCE_URL', env('BALANCE_URL', ''))),
        'delivery_url' => trim((string) env('SMS_DELIVERY_URL', 'https://api.keccel.com/sms/delivery.asp')),
        'token' => trim((string) env('SMS_TOKEN', '')),
        'from' => trim((string) env('SMS_FROM', 'DGRAD')),
        'timeout' => (int) env('SMS_TIMEOUT', 15),
    ],

];
