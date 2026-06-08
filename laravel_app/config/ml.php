<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ML Service base URL
    |--------------------------------------------------------------------------
    |
    | ML Service (FastAPI) URL that Laravel will send requests to.
    | Inside Docker, we use the service name from docker-compose (ml_service).
    |
    */

    'base_url' => env('ML_SERVICE_URL', 'http://ml_service:8000'),

    /*
    |--------------------------------------------------------------------------
    | HTTP timeout (seconds)
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('ML_SERVICE_TIMEOUT', 30),
];

