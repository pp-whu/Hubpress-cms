<?php

declare(strict_types=1);

/**
 * Web Routes
 *
 * Public-facing frontend routes for HuberCMS.
 * $router is injected by Application::loadRoutes().
 *
 * @var \HuberCMS\Core\Router $router
 */

use HuberCMS\Controllers\Auth\LoginController;
use HuberCMS\Controllers\Auth\RegisterController;
use HuberCMS\Controllers\Auth\PasswordController;
use HuberCMS\Controllers\Frontend\HomeController;
use HuberCMS\Controllers\Frontend\PageController;
use HuberCMS\Controllers\Frontend\PostController;
use HuberCMS\Controllers\Installer\InstallerController;
use HuberCMS\Middleware\AuthMiddleware;

// ============================================================
// Installer
// ============================================================
$router->group(['prefix' => '/install'], function ($router) {
    $router->get('',            InstallerController::class . '@index',    'install.index');
    $router->get('/step/{step}', InstallerController::class . '@step',    'install.step');
    $router->post('/process',   InstallerController::class . '@process',  'install.process');
});

// ============================================================
// Authentication
// ============================================================
$router->get('/login',           LoginController::class . '@showForm',   'login.form');
$router->post('/login',          LoginController::class . '@login',      'login.submit');
$router->post('/logout',         LoginController::class . '@logout',     'logout');

$router->get('/register',        RegisterController::class . '@showForm', 'register.form');
$router->post('/register',       RegisterController::class . '@register', 'register.submit');

$router->get('/password/forgot', PasswordController::class . '@showForgot', 'password.forgot');
$router->post('/password/forgot', PasswordController::class . '@sendReset', 'password.forgot.submit');
$router->get('/password/reset/{token}', PasswordController::class . '@showReset', 'password.reset');
$router->post('/password/reset', PasswordController::class . '@resetPassword', 'password.reset.submit');

// ============================================================
// Frontend — Public content
// ============================================================
$router->get('/',                 HomeController::class . '@index',       'home');
$router->get('/blog',             PostController::class . '@index',       'blog.index');
$router->get('/blog/{slug}',      PostController::class . '@show',        'blog.show');
$router->get('/seite/{slug}',     PageController::class . '@show',        'page.show');

// ============================================================
// Protected user area (requires login)
// ============================================================
$router->group([
    'prefix'     => '/mein-konto',
    'middleware' => [new AuthMiddleware($router->getContainer()->make(\HuberCMS\Core\Session::class))],
], function ($router) {
    $router->get('', \HuberCMS\Controllers\Frontend\AccountController::class . '@index', 'account.index');
    $router->post('/profil', \HuberCMS\Controllers\Frontend\AccountController::class . '@updateProfile', 'account.profile');
    $router->post('/passwort', \HuberCMS\Controllers\Frontend\AccountController::class . '@changePassword', 'account.password');
});
