<?php

return [
    'key' => env('SIGN_ME_API_KEY'),
    'sandbox' => env('SIGN_ME_SANDBOX', false),
    'logging' => [
        'start' => env('SIGN_ME_LOGGING_START', true),
        'path'  => env('SIGN_ME_LOGGING_PATH', storage_path('logs/signme.log')),
    ],
];
