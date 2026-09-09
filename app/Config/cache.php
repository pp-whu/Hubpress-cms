<?php

declare(strict_types=1);

/**
 * Cache Configuration
 *
 * Supports file, redis, and apcu drivers.
 *
 * @package HuberCMS
 */
return [
    'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
    'ttl'    => (int) ($_ENV['CACHE_TTL'] ?? 3600),
    'prefix' => 'hubercms_',

    'stores' => [
        'file' => [
            'path' => STORAGE_PATH . '/Cache',
        ],
        'redis' => [
            'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port'     => (int) ($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => 0,
        ],
        'apcu' => [],
    ],
];
