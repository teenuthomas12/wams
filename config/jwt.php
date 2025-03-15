<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is used to sign your tokens. Make sure to set it to something
    | random and secure. You can generate one using `php artisan key:generate`.
    |
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Time to Live
    |--------------------------------------------------------------------------
    |
    | The number of minutes that the token will be valid for.
    |
    */
    'ttl' => env('JWT_TTL', 60), // Default is 60 minutes.

    /*
    |--------------------------------------------------------------------------
    | JWT Refresh Time to Live
    |--------------------------------------------------------------------------
    |
    | The number of minutes that the refresh token will be valid for.
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // Default is 14 days.

    /*
    |--------------------------------------------------------------------------
    | JWT Hashing Algorithm
    |--------------------------------------------------------------------------
    |
    | Specify which algorithm should be used to encode the token.
    |
    */
    'algo' => env('JWT_ALGO', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | Required Claims
    |--------------------------------------------------------------------------
    |
    | These are the claims that will be checked when validating a token.
    |
    */
    'required_claims' => ['iss', 'iat', 'exp', 'sub'],

    /*
    |--------------------------------------------------------------------------
    | Optional Claims
    |--------------------------------------------------------------------------
    |
    | These are the optional claims that will be checked when validating a token.
    |
    */
    'optional_claims' => ['nbf', 'jti'],

    /*
    |--------------------------------------------------------------------------
    | JWT Encryption Key
    |--------------------------------------------------------------------------
    |
    | If you plan to use an encrypted JWT, provide the key here.
    |
    */
    'encrypt' => false, // Set this to true if you want to encrypt your JWT.

    /*
    |--------------------------------------------------------------------------
    | JWT Blacklist Enabled
    |--------------------------------------------------------------------------
    |
    | Set this to true to enable token blacklisting.
    |
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | JWT Blacklist Grace Period
    |--------------------------------------------------------------------------
    |
    | Set this to the grace period for the blacklisted token to still be valid.
    |
    */
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | JWT Storage
    |--------------------------------------------------------------------------
    |
    | Define how the tokens should be stored.
    |
    */
    'storage' => Tymon\JWTAuth\Providers\Storage\Fluent::class, // Default storage
];
