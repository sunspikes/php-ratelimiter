<?php
/**
 * Configure the adapter and cache drivers
 */

return [
    'adapter' => 'desarrolla',
    'desarrolla' => [
        'default_ttl' => 3600,
        'driver' => 'notcache',
        'notcache' => [
            // config for not cache
        ],
        'file' => [
            'cache_dir' => './data',
        ],
        'apc' => [
            // config for apc
        ],
        'memory' => [
            'limit' => 1000,
        ],
        'mongo' => [
            'server' => 'mongodb://localhost:27017',
        ],
        'mysql' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306
        ],
        'redis' => [
            // config for redis
        ],
        'memcache' => [
            // config for memcache
        ],
    ]
];