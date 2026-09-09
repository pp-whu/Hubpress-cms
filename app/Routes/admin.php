<?php

declare(strict_types=1);

/**
 * Admin Routes
 *
 * All routes for the HuberCMS admin panel.
 * Protected by AuthMiddleware + AdminMiddleware.
 *
 * @var \HuberCMS\Core\Router $router
 */

use HuberCMS\Controllers\Admin\DashboardController;
use HuberCMS\Controllers\Admin\UsersController;
use HuberCMS\Controllers\Admin\PagesController;
use HuberCMS\Controllers\Admin\PostsController;
use HuberCMS\Controllers\Admin\MediaController;
use HuberCMS\Controllers\Admin\ThemesController;
use HuberCMS\Controllers\Admin\WidgetsController;
use HuberCMS\Controllers\Admin\PluginsController;
use HuberCMS\Controllers\Admin\CommentsController;
use HuberCMS\Controllers\Admin\MenusController;
use HuberCMS\Controllers\Admin\SettingsController;
use HuberCMS\Controllers\Admin\LogsController;
use HuberCMS\Controllers\Admin\BackupController;
use HuberCMS\Controllers\Admin\SeoController;
use HuberCMS\Controllers\Admin\ProfileController;
use HuberCMS\Controllers\Admin\UpdateController;
use HuberCMS\Middleware\AuthMiddleware;
use HuberCMS\Middleware\AdminMiddleware;
use HuberCMS\Middleware\AdminOnlyMiddleware;

$container = $router->getContainer();
$session   = $container->make(\HuberCMS\Core\Session::class);

$router->group([
    'prefix'     => '/admin',
    'middleware' => [
        new AuthMiddleware($session),
        new AdminMiddleware($session),
    ],
], function ($router) use ($session) {

    // Dashboard
    $router->get('',               DashboardController::class . '@index',   'admin.dashboard');
    $router->post('/update/check', UpdateController::class . '@check',      'admin.update.check');
    $router->post('/update/install', UpdateController::class . '@install',  'admin.update.install');

    // Profile
    $router->get('/profil',        ProfileController::class . '@index',     'admin.profile');
    $router->post('/profil',       ProfileController::class . '@update',    'admin.profile.update');

    // Users
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/benutzer',          UsersController::class . '@index',  'admin.users');
        $router->get('/benutzer/neu',      UsersController::class . '@create', 'admin.users.create');
        $router->post('/benutzer',         UsersController::class . '@store',  'admin.users.store');
        $router->get('/benutzer/{id}',     UsersController::class . '@edit',   'admin.users.edit');
        $router->post('/benutzer/{id}',    UsersController::class . '@update', 'admin.users.update');
        $router->delete('/benutzer/{id}',  UsersController::class . '@delete', 'admin.users.delete');
    });

    // Pages
    $router->get('/seiten',                  PagesController::class . '@index',   'admin.pages');
    $router->get('/seiten/neu',              PagesController::class . '@create',  'admin.pages.create');
    $router->post('/seiten',                 PagesController::class . '@store',   'admin.pages.store');
    $router->get('/seiten/{id}/bearbeiten', PagesController::class . '@edit',    'admin.pages.edit');
    $router->post('/seiten/{id}',            PagesController::class . '@update',  'admin.pages.update');
    $router->delete('/seiten/{id}',          PagesController::class . '@delete',  'admin.pages.delete');

    // Posts
    $router->get('/beitraege',               PostsController::class . '@index',   'admin.posts');
    $router->get('/beitraege/neu',           PostsController::class . '@create',  'admin.posts.create');
    $router->post('/beitraege',              PostsController::class . '@store',   'admin.posts.store');
    $router->get('/beitraege/{id}/bearbeiten', PostsController::class . '@edit',  'admin.posts.edit');
    $router->post('/beitraege/{id}',         PostsController::class . '@update',  'admin.posts.update');
    $router->delete('/beitraege/{id}',       PostsController::class . '@delete',  'admin.posts.delete');

    // Media
    $router->get('/medien',                  MediaController::class . '@index',   'admin.media');
    $router->post('/medien/upload',          MediaController::class . '@upload',  'admin.media.upload');
    $router->delete('/medien/{id}',          MediaController::class . '@delete',  'admin.media.delete');
    $router->post('/medien/{id}/alt',        MediaController::class . '@updateAlt', 'admin.media.alt');

    // Comments
    $router->get('/kommentare',                  CommentsController::class . '@index',     'admin.comments');
    $router->post('/kommentare/{id}/approve',     CommentsController::class . '@approve',   'admin.comments.approve');
    $router->post('/kommentare/{id}/unapprove',   CommentsController::class . '@unapprove', 'admin.comments.unapprove');
    $router->post('/kommentare/{id}/spam',        CommentsController::class . '@spam',      'admin.comments.spam');
    $router->post('/kommentare/{id}/restore',     CommentsController::class . '@restore',   'admin.comments.restore');
    $router->delete('/kommentare/{id}',           CommentsController::class . '@delete',    'admin.comments.delete');

    // Menus
    $router->get('/menues',                  MenusController::class . '@index',       'admin.menus');
    $router->post('/menues',                 MenusController::class . '@store',       'admin.menus.store');
    $router->post('/menues/{id}',            MenusController::class . '@update',      'admin.menus.update');
    $router->delete('/menues/{id}',          MenusController::class . '@delete',      'admin.menus.delete');
    $router->get('/menues/{id}/items',       MenusController::class . '@items',       'admin.menus.items');
    $router->post('/menues/{id}/items',      MenusController::class . '@addItem',     'admin.menus.items.add');
    $router->delete('/menues/items/{id}',    MenusController::class . '@removeItem',  'admin.menus.items.remove');

    // Themes
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/themes',                    ThemesController::class . '@index',    'admin.themes');
        $router->post('/themes/{slug}/aktivieren', ThemesController::class . '@activate', 'admin.themes.activate');
    });

    // Widgets
    $router->get('/widgets',                     WidgetsController::class . '@index',   'admin.widgets');

    // Customizer
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/customizer',       \HuberCMS\Controllers\Admin\CustomizerController::class . '@index', 'admin.customizer');
        $router->post('/customizer/save', \HuberCMS\Controllers\Admin\CustomizerController::class . '@save',  'admin.customizer.save');
    });

    // Theme-Editor
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/theme-editor',       \HuberCMS\Controllers\Admin\ThemeEditorController::class . '@index', 'admin.theme_editor');
        $router->post('/theme-editor/save', \HuberCMS\Controllers\Admin\ThemeEditorController::class . '@save',  'admin.theme_editor.save');
    });

    // Plugin-Editor
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/plugin-editor', \HuberCMS\Controllers\Admin\PluginEditorController::class . '@index', 'admin.plugin_editor');
    });

    // Werkzeuge
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/werkzeuge',              \HuberCMS\Controllers\Admin\ToolsController::class . '@index',       'admin.tools');
        $router->get('/werkzeuge/import',       \HuberCMS\Controllers\Admin\ToolsController::class . '@import',      'admin.tools.import');
        $router->post('/werkzeuge/import',      \HuberCMS\Controllers\Admin\ToolsController::class . '@doImport',    'admin.tools.import.post');
        $router->get('/werkzeuge/export',       \HuberCMS\Controllers\Admin\ToolsController::class . '@export',      'admin.tools.export');
        $router->post('/werkzeuge/export',      \HuberCMS\Controllers\Admin\ToolsController::class . '@doExport',    'admin.tools.export.post');
        $router->get('/werkzeuge/site-health',  \HuberCMS\Controllers\Admin\ToolsController::class . '@health',      'admin.tools.health');
        $router->get('/werkzeuge/export-data',  \HuberCMS\Controllers\Admin\ToolsController::class . '@exportData',  'admin.tools.export_data');
        $router->post('/werkzeuge/export-data', \HuberCMS\Controllers\Admin\ToolsController::class . '@doExportData', 'admin.tools.export_data.post');
        $router->get('/werkzeuge/delete-data',  \HuberCMS\Controllers\Admin\ToolsController::class . '@deleteData',  'admin.tools.delete_data');
        $router->post('/werkzeuge/delete-data', \HuberCMS\Controllers\Admin\ToolsController::class . '@doDeleteData', 'admin.tools.delete_data.post');
    });

    // Plugins
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/plugins',                    PluginsController::class . '@index',     'admin.plugins');
        $router->post('/plugins/{slug}/aktivieren', PluginsController::class . '@activate',  'admin.plugins.activate');
        $router->post('/plugins/{slug}/deaktivieren', PluginsController::class . '@deactivate', 'admin.plugins.deactivate');
    });

    // SEO
    $router->get('/seo',                     SeoController::class . '@index',   'admin.seo');
    $router->post('/seo',                    SeoController::class . '@update',  'admin.seo.update');
    $router->get('/seo/sitemap',             SeoController::class . '@sitemap', 'admin.seo.sitemap');

    // Backup
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/backup',                 BackupController::class . '@index',    'admin.backup');
        $router->post('/backup/erstellen',      BackupController::class . '@create',   'admin.backup.create');
        $router->get('/backup/{file}/download', BackupController::class . '@download', 'admin.backup.download');
        $router->delete('/backup/{file}',       BackupController::class . '@delete',   'admin.backup.delete');
    });

    // Logs
    $router->get('/logs',                    LogsController::class . '@index',   'admin.logs');
    $router->get('/logs/{channel}',          LogsController::class . '@show',    'admin.logs.show');

    // Settings
    $router->group([
        'middleware' => [new AdminOnlyMiddleware($session)],
    ], function ($router) use ($session) {
        $router->get('/einstellungen',        SettingsController::class . '@index',  'admin.settings');
        $router->post('/einstellungen',       SettingsController::class . '@update', 'admin.settings.update');
        $router->get('/einstellungen/system', SettingsController::class . '@system', 'admin.settings.system');
    });
});
