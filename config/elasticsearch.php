<?php
    return [
        'connections' => [
            'default' => [
                'host' => env('ELASTICSEARCH_HOST', 'localhost'),
                'port' => env('ELASTICSEARCH_PORT', 9200),
                'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
                'user' => env('ELASTICSEARCH_USER', null),
                'pass' => env('ELASTICSEARCH_PASS', null),
            ],
        ],
        'retries' => [
            'verify_connection' => [
                'number_of_retries' => 2,
                'retry_interval' => 1000, // milliseconds
            ],
            'search' => [
                'number_of_retries' => 3,
                'retry_interval' => 500, // milliseconds
            ],
        ],
    ];