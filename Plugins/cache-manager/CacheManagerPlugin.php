<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\CacheManager;

use HuberCMS\Core\Container;
use HuberCMS\Core\Cache;
use HuberCMS\Core\EventDispatcher;
use HuberCMS\Core\Router;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;

/**
 * CacheManagerPlugin
 *
 * Seiten-Caching für das Frontend.
 * Äquivalent zu WP Super Cache für WordPress.
 */
class CacheManagerPlugin
{
    private const CACHE_TTL     = 3600;   // 1 Stunde
    private const CACHE_PREFIX  = 'page_';

    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        // Cache leeren wenn Beitrag/Seite gespeichert wird
        $events->listen('post.saved',  fn() => $this->flushPageCache($container));
        $events->listen('page.saved',  fn() => $this->flushPageCache($container));
        $events->listen('post.deleted', fn() => $this->flushPageCache($container));

        // Admin-Route: Cache leeren
        try {
            /** @var Router $router */
            $router = $container->make(Router::class);
            $router->post('/admin/cache/flush', function (Request $request) use ($container) {
                $this->flushPageCache($container);
                if ($request->wantsJson() || $request->isAjax()) {
                    return Response::json(['success' => true, 'message' => 'Cache geleert.']);
                }
                return Response::redirect('/admin/werkzeuge');
            });
        } catch (\Throwable) {
            // Router noch nicht verfügbar
        }

        // Dashboard-Widget mit Cache-Stats
        $events->listen('admin.dashboard.widgets', function (array $widgets) use ($container) {
            $stats = $this->getCacheStats($container);
            $widgets[] = [
                'id'      => 'cache-manager',
                'title'   => 'Cache Manager',
                'content' => $this->renderWidget($stats),
            ];
            return $widgets;
        });
    }

    /**
     * Gibt eine gecachete Response zurück oder null wenn kein Cache.
     */
    public function getCachedPage(string $url, Container $container): ?string
    {
        try {
            /** @var Cache $cache */
            $cache = $container->make(Cache::class);
            $key   = self::CACHE_PREFIX . md5($url);
            $cached = $cache->get($key);
            return is_string($cached) ? $cached : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Speichert eine Seite im Cache.
     */
    public function storePage(string $url, string $html, Container $container): void
    {
        try {
            /** @var Cache $cache */
            $cache = $container->make(Cache::class);
            $key   = self::CACHE_PREFIX . md5($url);
            $cache->set($key, $html, self::CACHE_TTL);
        } catch (\Throwable) {
            // Cache nicht verfügbar
        }
    }

    /**
     * Leert den gesamten Seiten-Cache.
     */
    public function flushPageCache(Container $container): void
    {
        try {
            /** @var Cache $cache */
            $cache = $container->make(Cache::class);
            $cache->flush();
        } catch (\Throwable) {
            // Cache nicht verfügbar
        }
    }

    /** @return array{entries:int, size_human:string} */
    private function getCacheStats(Container $container): array
    {
        $cacheDir = STORAGE_PATH . '/Cache';
        $files    = glob($cacheDir . '/' . self::CACHE_PREFIX . '*.cache') ?: [];
        $size     = array_sum(array_map('filesize', $files));

        return [
            'entries'    => count($files),
            'size_human' => $size > 1048576
                ? round($size / 1048576, 1) . ' MB'
                : round($size / 1024, 1) . ' KB',
        ];
    }

    private function renderWidget(array $stats): string
    {
        return sprintf(
            '<div class="d-flex gap-4 mb-3">'
            . '<div><div class="fw-bold fs-5">%d</div><div class="text-muted small">gecachte Seiten</div></div>'
            . '<div><div class="fw-bold fs-5">%s</div><div class="text-muted small">Cache-Größe</div></div>'
            . '</div>'
            . '<form method="POST" action="/admin/cache/flush">'
            . '<input type="hidden" name="_token" value="">'
            . '<button type="submit" class="btn btn-outline-danger btn-sm">'
            . '<i class="bi bi-trash me-1"></i>Cache leeren</button></form>',
            $stats['entries'],
            htmlspecialchars($stats['size_human'], ENT_QUOTES)
        );
    }
}
