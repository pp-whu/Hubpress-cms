<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * ThemeLoader
 *
 * Loads and manages the active frontend theme.
 * Themes live in /Themes/{slug}/ and must have a theme.php manifest.
 *
 * Theme structure:
 *   Themes/
 *     default/
 *       theme.php         (manifest: name, version, author, screenshot)
 *       templates/        (PHP template files)
 *       assets/
 *         css/
 *         js/
 *         images/
 *       inc/              (theme helper files)
 *         functions.php
 *
 * @package HuberCMS\Core
 */
final class ThemeLoader
{
    private string $activeSlug = 'default';

    /** @var array<string, mixed>|null Active theme manifest */
    private ?array $manifest = null;

    public function __construct(
        private readonly Config $config,
        private readonly string $basePath
    ) {
    }

    // =========================================================
    // Activation
    // =========================================================

    /**
     * Activates a theme by slug. Validates existence and loads manifest.
     */
    public function activate(string $slug): void
    {
        $themePath = $this->themePath($slug);

        if (!is_dir($themePath)) {
            throw new \RuntimeException("Theme [{$slug}] not found at [{$themePath}].");
        }

        $manifestFile = $themePath . '/theme.php';
        if (!file_exists($manifestFile)) {
            throw new \RuntimeException("Theme [{$slug}] is missing a theme.php manifest.");
        }

        $this->activeSlug = $slug;
        $this->manifest   = require $manifestFile;

        // Load optional theme functions
        $functionsFile = $themePath . '/inc/functions.php';
        if (file_exists($functionsFile)) {
            require_once $functionsFile;
        }
    }

    /**
     * Returns the active theme slug.
     */
    public function active(): string
    {
        return $this->activeSlug;
    }

    /**
     * Returns the full filesystem path to the active theme's templates.
     */
    public function templatesPath(): string
    {
        return $this->themePath($this->activeSlug) . '/templates';
    }

    /**
     * Returns the public URL for a theme asset.
     */
    public function assetUrl(string $asset): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return "{$base}/themes/{$this->activeSlug}/{$asset}";
    }

    // =========================================================
    // Discovery
    // =========================================================

    /**
     * Returns manifests of all installed themes.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        $themes = [];
        $themesBase = $this->basePath . '/Themes';

        foreach (glob($themesBase . '/*/theme.php') ?: [] as $manifestPath) {
            $slug = basename(dirname($manifestPath));
            $manifest = @require $manifestPath;

            if (is_array($manifest)) {
                $themes[$slug] = array_merge($manifest, [
                    'slug'   => $slug,
                    'active' => $slug === $this->activeSlug,
                    'screenshot' => $this->screenshotUrl($slug),
                ]);
            }
        }

        return $themes;
    }

    // =========================================================
    // Private helpers
    // =========================================================

    private function themePath(string $slug): string
    {
        // Prevent path traversal
        $slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $slug);
        return $this->basePath . '/Themes/' . $slug;
    }

    private function screenshotUrl(string $slug): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return "{$base}/themes/{$slug}/screenshot.png";
    }
}
