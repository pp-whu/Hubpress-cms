<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * PluginLoader
 *
 * Discovers and loads active plugins from the /Plugins/ directory.
 * Each plugin must have a plugin.php manifest and a main class
 * implementing PluginInterface.
 *
 * Plugin structure:
 *   Plugins/
 *     my-plugin/
 *       plugin.php        (manifest: name, version, author, main)
 *       MyPlugin.php      (main class)
 *       assets/
 *
 * @package HuberCMS\Core
 */
final class PluginLoader
{
    /** @var array<string, object> Loaded plugin instances keyed by slug */
    private array $loaded = [];

    /** @var string[] Slugs of failed plugins */
    private array $failed = [];

    public function __construct(
        private readonly Container $container,
        private readonly string   $basePath
    ) {
    }

    // =========================================================
    // Loading
    // =========================================================

    /**
     * Loads all active plugins. Active state is read from the settings table
     * (or all plugins if no DB is available yet during install).
     */
    public function loadActive(): void
    {
        $pluginDir = $this->basePath . '/Plugins';

        if (!is_dir($pluginDir)) {
            return;
        }

        $activeSlugs = $this->getActiveSlugs();

        foreach (glob($pluginDir . '/*/plugin.php') ?: [] as $manifestPath) {
            $slug = basename(dirname($manifestPath));

            if (!empty($activeSlugs) && !in_array($slug, $activeSlugs, true)) {
                continue;
            }

            $this->loadPlugin($slug, $manifestPath);
        }
    }

    /**
     * Loads a specific plugin by slug.
     */
    public function loadPlugin(string $slug, string $manifestPath): bool
    {
        try {
            $manifest = require $manifestPath;

            if (!is_array($manifest)) {
                throw new \RuntimeException("Plugin [{$slug}] manifest must return an array.");
            }

            $mainClass = $manifest['main'] ?? null;

            if ($mainClass === null || !class_exists($mainClass)) {
                throw new \RuntimeException("Plugin [{$slug}] main class [{$mainClass}] not found.");
            }

            $instance = $this->container->make($mainClass);
            $instance->boot($this->container);
            $this->loaded[$slug] = $instance;

            return true;
        } catch (\Throwable $e) {
            $this->failed[] = $slug;
            error_log("Plugin [{$slug}] failed to load: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // Discovery
    // =========================================================

    /**
     * Returns manifests of all installed plugins.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        $plugins = [];
        $pluginDir = $this->basePath . '/Plugins';

        foreach (glob($pluginDir . '/*/plugin.php') ?: [] as $manifestPath) {
            $slug = basename(dirname($manifestPath));
            $manifest = @require $manifestPath;

            if (is_array($manifest)) {
                $plugins[$slug] = array_merge($manifest, [
                    'slug'   => $slug,
                    'active' => isset($this->loaded[$slug]),
                ]);
            }
        }

        return $plugins;
    }

    /** @return string[] */
    public function getLoaded(): array
    {
        return array_keys($this->loaded);
    }

    /** @return string[] */
    public function getFailed(): array
    {
        return $this->failed;
    }

    // =========================================================
    // Private
    // =========================================================

    /** @return string[] */
    private function getActiveSlugs(): array
    {
        // During installation, DB may not be ready — load all
        try {
            /** @var Database $db */
            $db = $this->container->make(Database::class);
            $prefix = $db->prefix();
            $rows = $db->select(
                "SELECT value FROM `{$prefix}settings` WHERE `key` = 'active_plugins' LIMIT 1"
            );

            if (!empty($rows)) {
                return json_decode($rows[0]['value'] ?? '[]', true) ?? [];
            }
        } catch (\Throwable) {
            // DB not available — load all during install
        }

        return [];
    }
}
