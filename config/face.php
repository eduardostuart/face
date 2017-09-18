<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Face provider
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the face providers below you wish
    | to use as your default.
    |
    */
    'default_provider' => 'face_plus_plus',

    /*
    |--------------------------------------------------------------------------
    | Face Providers
    |--------------------------------------------------------------------------
    |
    */
    'providers' => [
        /*
        | Face++ Provider
        | faceplusplus.com
        */
        'face_plus_plus' => [
            'api_key' => env('FACEPLUS_API_KEY'),
            'api_secret' => env('FACEPLUS_API_SECRET'),
        ],
    ],
];
