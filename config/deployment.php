<?php

return [
    'admin' => [
        'name' => env('ADMIN_NAME', 'System Administrator'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'users_json' => env('PRODUCTION_USERS_JSON'),
    'user_password' => env('PRODUCTION_USERS_PASSWORD'),
];
