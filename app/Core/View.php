<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use HuberCMS\Core\TemplateEngine;
use HuberCMS\Core\Session;

/**
 * View
 *
 * Facade for rendering templates. Injects global view data
 * (CSRF token, flash messages, current user, etc.) automatically.
 *
 * Usage in controllers:
 *   return View::make('admin.dashboard', ['stats' => $stats]);
 *
 * @package HuberCMS\Core
 */
final class View
{
    /** @var array<string, mixed> Data shared across all views */
    private static array $shared = [];

    private static ?TemplateEngine $engine = null;
    private static ?Session $session = null;

    /**
     * Bootstraps the View layer with the template engine and session.
     */
    public static function boot(TemplateEngine $engine, Session $session): void
    {
        self::$engine  = $engine;
        self::$session = $session;
    }

    /**
     * Shares a value across all views rendered in this request.
     */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Renders a template and returns a Response.
     *
     * @param array<string, mixed> $data
     */
    public static function make(string $template, array $data = [], int $status = 200): Response
    {
        $merged = array_merge(self::$shared, self::globalData(), $data);
        $content = self::getEngine()->render($template, $merged);
        return Response::html($content, $status);
    }

    /**
     * Returns the rendered string of a template (for embedding).
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): string
    {
        $merged = array_merge(self::$shared, self::globalData(), $data);
        return self::getEngine()->render($template, $merged);
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Returns data automatically injected into every view.
     *
     * @return array<string, mixed>
     */
    private static function globalData(): array
    {
        $session = self::$session;

        return [
            '__csrf'    => $session?->csrfToken() ?? '',
            '__flash'   => [
                'success' => $session?->getFlash('success'),
                'error'   => $session?->getFlash('error'),
                'warning' => $session?->getFlash('warning'),
                'info'    => $session?->getFlash('info'),
            ],
            '__user'    => $session?->get('auth_user'),
            '__locale'  => $session?->get('locale', $_ENV['APP_LOCALE'] ?? 'de'),
            '__appName' => $_ENV['APP_NAME'] ?? 'HuberCMS',
            '__appUrl'  => rtrim($_ENV['APP_URL'] ?? 'https://cms.ddev.site', '/'),
            '__version' => HUBERCMS_VERSION,
        ];
    }

    private static function getEngine(): TemplateEngine
    {
        if (self::$engine === null) {
            throw new \RuntimeException('View::boot() has not been called. Did the Application boot?');
        }
        return self::$engine;
    }
}
