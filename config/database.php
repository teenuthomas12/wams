<?php

return [
    'default' => env('DB_CONNECTION', 'sqlsrv'),

    'connections' => [
        'auth' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],
        'wams' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB1_HOST', 'localhost'),
            'port'     => env('DB1_PORT', '1433'),
            'database' => env('DB1_DATABASE'),
            'username' => env('DB1_USERNAME'),
            'password' => env('DB1_PASSWORD'),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],
        // Add other database connections if needed
    ],

    'migrations' => 'migrations',
];
