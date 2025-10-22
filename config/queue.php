<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => env('QUEUE_FAILED_NAME', 'default'),
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

        // Specialized queues for administrative alerts
        'security-alerts' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'security-alerts',
            'retry_after' => 60,
            'after_commit' => false,
        ],
        
        'security-high-alerts' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'security-high-alerts',
            'retry_after' => 30,
            'after_commit' => false,
        ],
        
        'system-alerts' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'system-alerts',
            'retry_after' => 90,
            'after_commit' => false,
        ],
        
        'business-alerts' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'business-alerts',
            'retry_after' => 90,
            'after_commit' => false,
        ],
        
        'monitoring' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'monitoring',
            'retry_after' => 120,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
