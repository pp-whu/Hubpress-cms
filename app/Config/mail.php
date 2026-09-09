<?php

declare(strict_types=1);

/**
 * Mail Configuration
 *
 * @package HuberCMS
 */
return [
    'driver'     => $_ENV['MAIL_DRIVER'] ?? 'log',
    'host'       => $_ENV['MAIL_HOST'] ?? 'localhost',
    'port'       => (int) ($_ENV['MAIL_PORT'] ?? 1025),
    'username'   => $_ENV['MAIL_USERNAME'] ?? '',
    'password'   => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? '',
    'from'       => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@hubercms.io',
        'name'    => $_ENV['MAIL_FROM_NAME'] ?? 'HuberCMS',
    ],
    'template_path' => VIEWS_PATH . '/emails',
];
