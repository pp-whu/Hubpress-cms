<?php

declare(strict_types=1);

/**
 * HuberCMS - Front Controller
 *
 * This is the single entry point for all HTTP requests.
 * It bootstraps the application, loads dependencies,
 * and dispatches the request to the appropriate handler.
 *
 * @package HuberCMS
 * @version 1.0.0
 * @license MIT
 */

// ============================================================
// Define constants
// ============================================================

define('HUBERCMS_VERSION', '1.0.0');
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', APP_PATH . '/Storage');
define('CONFIG_PATH', APP_PATH . '/Config');
define('VIEWS_PATH', APP_PATH . '/Views');
define('THEMES_PATH', BASE_PATH . '/Themes');
define('PLUGINS_PATH', BASE_PATH . '/Plugins');

define('START_TIME', microtime(true));
define('START_MEMORY', memory_get_usage(true));

// ============================================================
// Check PHP version
// ============================================================

if (PHP_VERSION_ID < 80400) {
    http_response_code(500);
    die(sprintf(
        'HuberCMS requires PHP 8.4 or higher. Current version: %s',
        PHP_VERSION
    ));
}

// ============================================================
// Load Composer autoloader
// ============================================================

$autoloaderPath = BASE_PATH . '/vendor/autoload.php';

if (!file_exists($autoloaderPath)) {
    http_response_code(500);
    die(
        '<h1>Dependencies not installed</h1>' .
        '<p>Please run <code>composer install</code> in the project root.</p>'
    );
}

require_once $autoloaderPath;

// ============================================================
// Check if installation is required
// ============================================================

$installLockFile = PUBLIC_PATH . '/install.lock';

$isInstalled = file_exists($installLockFile);

if (!$isInstalled && !str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/install')) {
    header('Location: /install', true, 302);
    exit;
}

// ============================================================
// Bootstrap and run the application
// ============================================================

use HuberCMS\Core\Application;
use HuberCMS\Core\ExceptionHandler;

try {
    $app = Application::getInstance(BASE_PATH);
    $app->boot();
    $app->run();
} catch (\Throwable $e) {
    ExceptionHandler::handle($e);
}
