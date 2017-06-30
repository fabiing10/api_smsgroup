<?php

return [
    'defaults' => [
        'supportsCredentials' => true,
        'allowedOrigins' => ['*'],
        'allowedHeaders' => ['*'],
        'allowedMethods' => ['*'],
        'exposedHeaders' => ['*'],
        'maxAge' => 0,
        'hosts' => ['*'],
    ],

    'paths' => [
        '*' => [
            'allowedOrigins' => ['*'],
            'allowedHeaders' => ['*'],
            'allowedMethods' => ['*'],
            'maxAge' => 3600,
        ],
    ],
];
