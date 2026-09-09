<?php

declare(strict_types=1);

/**
 * Session Configuration
 *
 * @package HuberCMS
 */
return [
    'driver'   => $_ENV['SESSION_DRIVER'] ?? 'file',
    'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
    'name'     => 'hubercms_session',
    'path'     => '/',
    'domain'   => null,
    'secure'   => filter_var($_ENV['SESSION_SECURE'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'httponly' => filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Lax',
    'save_path' => STORAGE_PATH . '/Sessions',
];
