<?php

declare(strict_types=1);

/**
 * API Routes (v1)
 *
 * RESTful JSON API — protected by JWT or API Key.
 * All routes return application/json.
 *
 * @var \HuberCMS\Core\Router $router
 */

use HuberCMS\Controllers\Api\AuthApiController;
use HuberCMS\Controllers\Api\PostsApiController;
use HuberCMS\Controllers\Api\PagesApiController;
use HuberCMS\Controllers\Api\MediaApiController;
use HuberCMS\Controllers\Api\UsersApiController;
use HuberCMS\Middleware\ApiAuthMiddleware;
use HuberCMS\Middleware\AdminApiMiddleware;

$router->group([
    'prefix' => '/api/v1',
], function ($router) {

    // --------------------------------------------------------
    // Public API endpoints (no auth required)
    // --------------------------------------------------------
    $router->post('/auth/login',     AuthApiController::class . '@login',   'api.auth.login');
    $router->post('/auth/refresh',   AuthApiController::class . '@refresh', 'api.auth.refresh');

    // --------------------------------------------------------
    // Protected API endpoints — require valid JWT or API key
    // --------------------------------------------------------
    $router->group([
        'middleware' => [new ApiAuthMiddleware($router->getContainer())],
    ], function ($router) {

        // Auth
        $router->post('/auth/logout',    AuthApiController::class . '@logout',  'api.auth.logout');
        $router->get('/auth/me',         AuthApiController::class . '@me',      'api.auth.me');

        // Posts
        $router->get('/posts',           PostsApiController::class . '@index',  'api.posts.index');
        $router->get('/posts/{id}',      PostsApiController::class . '@show',   'api.posts.show');
        $router->group([
            'middleware' => [new AdminApiMiddleware($router->getContainer())],
        ], function ($router) {
            $router->post('/posts',         PostsApiController::class . '@store',  'api.posts.store');
            $router->put('/posts/{id}',     PostsApiController::class . '@update', 'api.posts.update');
            $router->delete('/posts/{id}',  PostsApiController::class . '@delete', 'api.posts.delete');
        });

        // Pages
        $router->get('/pages',           PagesApiController::class . '@index',  'api.pages.index');
        $router->get('/pages/{id}',      PagesApiController::class . '@show',   'api.pages.show');

        // Media
        $router->get('/media',           MediaApiController::class . '@index',  'api.media.index');
        $router->group([
            'middleware' => [new AdminApiMiddleware($router->getContainer())],
        ], function ($router) {
            $router->post('/media/upload',  MediaApiController::class . '@upload', 'api.media.upload');
            $router->delete('/media/{id}',  MediaApiController::class . '@delete', 'api.media.delete');
        });

        // Users (admin only)
        $router->group([
            'middleware' => [new AdminApiMiddleware($router->getContainer())],
        ], function ($router) {
            $router->get('/users',      UsersApiController::class . '@index', 'api.users.index');
            $router->get('/users/{id}', UsersApiController::class . '@show',  'api.users.show');
        });
    });
});
