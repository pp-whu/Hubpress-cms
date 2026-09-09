<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use HuberCMS\Core\Container;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Router;
use HuberCMS\Core\Config;
use HuberCMS\Core\Session;
use HuberCMS\Core\Logger;
use HuberCMS\Core\EventDispatcher;
use HuberCMS\Core\PluginLoader;
use HuberCMS\Core\ThemeLoader;
use HuberCMS\Core\Cache;
use HuberCMS\Core\Database;
use HuberCMS\Core\TemplateEngine;
use HuberCMS\Core\View;
use HuberCMS\Core\Model;
use HuberCMS\Middleware\CsrfMiddleware;
use HuberCMS\Middleware\RateLimitMiddleware;

/**
 * Application
 *
 * Central application class. Implements the Singleton pattern to ensure
 * only one instance exists. Responsible for bootstrapping all core
 * services and dispatching the HTTP request through the router.
 *
 * @package HuberCMS\Core
 */
final class Application
{
    private static ?self $instance = null;

    private Container $container;
    private bool $booted = false;

    /**
     * Private constructor — use getInstance().
     */
    private function __construct(private readonly string $basePath)
    {
        $this->container = new Container();
    }

    /**
     * Returns the singleton Application instance.
     */
    public static function getInstance(string $basePath = ''): self
    {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }

        return self::$instance;
    }

    /**
     * Bootstraps all core services and registers them in the DI container.
     * Must be called once before run().
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->registerBaseBindings();
        $this->loadEnvironment();
        $this->registerCoreServices();
        $this->loadRoutes();
        $this->loadPlugins();

        $this->booted = true;
    }

    /**
     * Handles the incoming HTTP request and sends the response.
     */
    public function run(): void
    {
        // Start the session HERE — before any output buffering begins.
        // This ensures the session cookie is set reliably on every request
        // and the CSRF token is readable when templates are rendered.
        $this->container->make(Session::class)->start();

        /** @var Request $request */
        $request = $this->container->make(Request::class);

        /** @var Router $router */
        $router = $this->container->make(Router::class);

        $response = $router->dispatch($request);
        $response->send();
    }

    /**
     * Returns the DI container.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Returns the base path of the application.
     */
    public function getBasePath(string $suffix = ''): string
    {
        return $suffix ? $this->basePath . DIRECTORY_SEPARATOR . ltrim($suffix, '/\\') : $this->basePath;
    }

    // =========================================================
    // Private bootstrap methods
    // =========================================================

    /**
     * Registers the Application and Container themselves in the container.
     */
    private function registerBaseBindings(): void
    {
        $this->container->instance(self::class, $this);
        $this->container->instance(Container::class, $this->container);
    }

    /**
     * Loads .env file using vlucas/phpdotenv.
     * createMutable() ensures .env values OVERRIDE any DDEV-injected container env vars.
     */
    private function loadEnvironment(): void
    {
        $envFile = $this->basePath . '/.env';

        if (file_exists($envFile)) {
            $dotenv = \Dotenv\Dotenv::createMutable($this->basePath);
            $dotenv->safeLoad();
        }
    }

    /**
     * Registers all core framework services in the container.
     */
    private function registerCoreServices(): void
    {
        // Config — must be first
        $this->container->singleton(Config::class, fn() => new Config($this->basePath));

        /** @var Config $config */
        $config = $this->container->make(Config::class);

        // Logger
        $this->container->singleton(
            Logger::class,
            fn() => new Logger($config)
        );

        // Database
        $this->container->singleton(
            Database::class,
            fn() => new Database($config)
        );

        // Cache
        $this->container->singleton(
            Cache::class,
            fn() => new Cache($config)
        );

        $this->container->singleton(
            \HuberCMS\Services\UpdateService::class,
            fn() => new \HuberCMS\Services\UpdateService($this->basePath)
        );

        // Session
        $this->container->singleton(
            Session::class,
            fn() => new Session($config)
        );

        // Event Dispatcher
        $this->container->singleton(
            EventDispatcher::class,
            fn() => new EventDispatcher()
        );

        // Theme Loader
        $this->container->singleton(
            ThemeLoader::class,
            fn() => new ThemeLoader($config, $this->basePath)
        );

        // Request (built fresh from globals)
        $this->container->bind(
            Request::class,
            fn() => Request::createFromGlobals()
        );

        // Response
        $this->container->bind(
            Response::class,
            fn() => new Response()
        );

        // Template Engine
        $this->container->singleton(
            TemplateEngine::class,
            fn() => new TemplateEngine(VIEWS_PATH)
        );

        // Router (with CSRF + rate limit middleware)
        $this->container->singleton(Router::class, function () use ($config) {
            $router = new Router($this->container);
            $router->addMiddleware(new RateLimitMiddleware($config));
            $router->addMiddleware(new CsrfMiddleware($this->container->make(Session::class)));
            return $router;
        });

        // Boot View layer — injects engine + session for global template data
        View::boot(
            $this->container->make(TemplateEngine::class),
            $this->container->make(Session::class)
        );

        // Share all menus (keyed by location) with every view
        try {
            $db  = $this->container->make(Database::class);
            $p   = $db->prefix();

            // Auto-seed if no menu items exist (covers empty DB or broken state)
            $itemCount = (int) ($db->select("SELECT COUNT(*) AS c FROM `{$p}menu_items`")[0]['c'] ?? 0);
            if ($itemCount === 0) {
                // Clear any incomplete menu records and re-seed from scratch
                $db->statement("DELETE FROM `{$p}menu_items`");
                $db->statement("DELETE FROM `{$p}menus`");
                self::seedDefaultMenus($db, $p);
            }

            $rows = $db->select(
                "SELECT m.id AS menu_id, m.name AS menu_name, m.slug, m.location,
                        mi.id, mi.label, mi.url, mi.target, mi.icon, mi.sort_order
                 FROM `{$p}menus` m
                 LEFT JOIN `{$p}menu_items` mi ON mi.menu_id = m.id
                 ORDER BY m.id, mi.sort_order, mi.id"
            );
            $menus = [];
            foreach ($rows as $row) {
                $key = $row['location'] ?? $row['slug'];
                if (!isset($menus[$key])) {
                    $menus[$key] = [
                        'id'    => $row['menu_id'],
                        'name'  => $row['menu_name'],
                        'slug'  => $row['slug'],
                        'items' => [],
                    ];
                }
                if (!empty($row['label'])) {
                    $menus[$key]['items'][] = [
                        'label'  => $row['label'],
                        'url'    => $row['url'],
                        'target' => $row['target'] ?? '_self',
                        'icon'   => $row['icon'] ?? '',
                    ];
                }
            }
            View::share('__menus', $menus);
        } catch (\Throwable) {
            View::share('__menus', []);
        }

        // Inject DB into all Model classes (lazy — connection is only made on first query)
        Model::setDatabase($this->container->make(Database::class));
    }

    /**
     * Loads all route definitions from the Routes/ directory.
     */
    private function loadRoutes(): void
    {
        /** @var Router $router */
        $router = $this->container->make(Router::class);

        $routeFiles = [
            APP_PATH . '/Routes/web.php',
            APP_PATH . '/Routes/api.php',
            APP_PATH . '/Routes/admin.php',
        ];

        foreach ($routeFiles as $file) {
            if (file_exists($file)) {
                // Routes get access to the router via closure binding
                (static function (Router $router) use ($file) {
                    require $file;
                })($router);
            }
        }
    }

    /**
     * Loads all active plugins via the PluginLoader.
     */
    private function loadPlugins(): void
    {
        $pluginLoader = new PluginLoader($this->container, $this->basePath);
        $pluginLoader->loadActive();
        $this->container->instance(PluginLoader::class, $pluginLoader);

        // Dispatcher für Template-Hooks (z. B. frontend.body.end) bereitstellen
        View::share('__events', $this->container->make(EventDispatcher::class));
    }

    /**
     * Seeds the four default theme menus with their items.
     * Called automatically the first time the application runs with an empty menus table.
     */
    private static function seedDefaultMenus(Database $db, string $p): void
    {
        $menus = [
            ['Hauptnavigation', 'hauptnavigation', 'primary-nav'],
            ['Metabar-Menü',    'metabar-menu',    'meta-bar'],
            ['Socialbar-Menü',  'socialbar-menu',  'social-bar'],
            ['Footer-Menü',     'footer-menu',     'footer-nav'],
        ];

        $ids = [];
        foreach ($menus as [$name, $slug, $location]) {
            $id = $db->insert(
                "INSERT IGNORE INTO `{$p}menus` (name, slug, location) VALUES (?, ?, ?)",
                [$name, $slug, $location]
            );
            if (!$id) {
                $row = $db->select("SELECT id FROM `{$p}menus` WHERE slug = ?", [$slug]);
                $id  = (int) ($row[0]['id'] ?? 0);
            }
            $ids[$slug] = (int) $id;
        }

        $ins = fn(int $menuId, string $label, string $url, string $target, ?string $icon, int $sort) =>
            $db->insert(
                "INSERT INTO `{$p}menu_items` (menu_id, label, url, target, icon, sort_order) VALUES (?,?,?,?,?,?)",
                [$menuId, $label, $url, $target, $icon, $sort]
            );

        // Hauptnavigation
        $nav = $ids['hauptnavigation'] ?? 0;
        if ($nav) {
            $ins($nav, 'Startseite', '/',               '_self', null, 1);
            $ins($nav, 'Blog',       '/blog',           '_self', null, 2);
            $ins($nav, 'Über uns',   '/seite/ueber-uns','_self', null, 3);
            $ins($nav, 'Leistungen', '/seite/leistungen','_self',null, 4);
            $ins($nav, 'Kontakt',    '/seite/kontakt',  '_self', null, 5);
        }

        // Metabar
        $meta = $ids['metabar-menu'] ?? 0;
        if ($meta) {
            $ins($meta, '+49 89 123 456',   'tel:+4989123456',        '_self', 'bi-telephone-fill', 1);
            $ins($meta, 'info@hubpress.de', 'mailto:info@hubpress.de','_self', 'bi-envelope-fill',  2);
            $ins($meta, 'Mo–Fr 9–17 Uhr',  '#',                      '_self', 'bi-clock-fill',     3);
        }

        // Socialbar
        $soc = $ids['socialbar-menu'] ?? 0;
        if ($soc) {
            $ins($soc, 'Facebook',   '#', '_blank', 'bi-facebook',  1);
            $ins($soc, 'X/Twitter',  '#', '_blank', 'bi-twitter-x', 2);
            $ins($soc, 'Instagram',  '#', '_blank', 'bi-instagram', 3);
            $ins($soc, 'LinkedIn',   '#', '_blank', 'bi-linkedin',  4);
            $ins($soc, 'YouTube',    '#', '_blank', 'bi-youtube',   5);
        }

        // Footer
        $foot = $ids['footer-menu'] ?? 0;
        if ($foot) {
            $ins($foot, 'Startseite',        '/',                       '_self',  null, 1);
            $ins($foot, 'Blog',              '/blog',                   '_self',  null, 2);
            $ins($foot, 'Über uns',          '/seite/ueber-uns',        '_self',  null, 3);
            $ins($foot, 'Leistungen',        '/seite/leistungen',       '_self',  null, 4);
            $ins($foot, 'Kontakt',           '/seite/kontakt',          '_self',  null, 5);
            $ins($foot, 'Impressum',         '/seite/impressum',        '_self',  null, 6);
            $ins($foot, 'Datenschutz',       '/seite/datenschutz',      '_self',  null, 7);
            $ins($foot, 'AGB',               '/seite/agb',              '_self',  null, 8);
            $ins($foot, 'Cookie-Richtlinie', '/seite/cookie-richtlinie','_self',  null, 9);
            $ins($foot, 'Sitemap',           '/sitemap.xml',            '_blank', null, 10);
        }
    }
}
