<?php

declare(strict_types=1);

/**
 * Application Configuration
 *
 * Core settings for HuberCMS. All sensitive values should be
 * defined in the .env file and referenced via getenv() here.
 *
 * @package HuberCMS
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Application Name & Version
    |--------------------------------------------------------------------------
    */
    'name'    => $_ENV['APP_NAME'] ?? 'HuberCMS',
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    | Values: development | staging | production
    */
    'env'   => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'),

    /*
    |--------------------------------------------------------------------------
    | Application Key
    |--------------------------------------------------------------------------
    | Used for encryption. Generate with the installer or CLI.
    | MUST be a 64-character random string in production.
    */
    'key' => $_ENV['APP_KEY'] ?? '',

    /*
    |--------------------------------------------------------------------------
    | Timezone & Locale
    |--------------------------------------------------------------------------
    */
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    'locale'   => $_ENV['APP_LOCALE'] ?? 'en',
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Installed Flag
    |--------------------------------------------------------------------------
    */
    'installed' => file_exists(PUBLIC_PATH . '/install.lock'),

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_size'      => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760),
        'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,webp,avif,pdf'),
        'image_quality' => (int) ($_ENV['IMAGE_QUALITY'] ?? 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'security' => [
        'bcrypt_rounds'   => (int) ($_ENV['BCRYPT_ROUNDS'] ?? 12),
        'csrf_ttl'        => (int) ($_ENV['CSRF_TTL'] ?? 7200),
        'rate_limit_max'  => (int) ($_ENV['RATE_LIMIT_MAX'] ?? 100),
        'rate_limit_window' => (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    */
    'supported_locales' => ['de', 'en', 'fr', 'es'],

    /*
    |--------------------------------------------------------------------------
    | Admin Path
    |--------------------------------------------------------------------------
    | The URI prefix for the admin panel.
    */
    'admin_path' => 'admin',
];
