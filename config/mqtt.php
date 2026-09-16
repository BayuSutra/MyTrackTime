<?php

return [
    'default' => env('MQTT_CONNECTION', 'default'),

    'connections' => [
        'default' => [
            'host'         => env('MQTT_HOST', 'localhost'),
            'port'         => env('MQTT_PORT', 1883),
            'username'     => env('MQTT_USERNAME', ''),
            'password'     => env('MQTT_PASSWORD', ''),
            'client_id'    => env('MQTT_CLIENT_ID', 'laravel_app'),
            'ssl'          => env('MQTT_SSL', true),
            'keep_alive'   => env('MQTT_KEEP_ALIVE', 60),
            'clean_session'=> true,
        ],
    ],
];