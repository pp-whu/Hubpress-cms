<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use InvalidArgumentException;

/**
 * Config
 *
 * Loads PHP configuration files from the app/Config/ directory.
 * Supports dot-notation access (e.g., 'database.host').
 * Values are merged with environment variables via $_ENV / getenv().
 *
 * @package HuberCMS\Core
 */
final class Config
{
    /** @var array<string, mixed> Flat config cache */
    private array $items = [];

    /** @var array<string, bool> Tracks which config files have been loaded */
    private array $loaded = [];

    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * Returns a config value using dot notation.
     *
     * @param string $key     e.g. 'database.host' or 'app.debug'
     * @param mixed  $default Returned when key is not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Ensure the file for this key group is loaded
        $group = explode('.', $key)[0];
        $this->loadGroup($group);

        // Walk the dot path
        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Sets a config value at runtime (does not persist to file).
     */
    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $current = &$this->items;

        foreach ($segments as $i => $segment) {
            if ($i === count($segments) - 1) {
                $current[$segment] = $value;
            } else {
                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    $current[$segment] = [];
                }
                $current = &$current[$segment];
            }
        }
    }

    /**
     * Checks whether a config key exists.
     */
    public function has(string $key): bool
    {
        return $this->get($key, '__NOT_FOUND__') !== '__NOT_FOUND__';
    }

    /**
     * Returns all config items for a group (e.g., 'database').
     *
     * @return array<string, mixed>
     */
    public function all(string $group): array
    {
        $this->loadGroup($group);
        return $this->items[$group] ?? [];
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Loads a config file group if not already loaded.
     */
    private function loadGroup(string $group): void
    {
        if (isset($this->loaded[$group])) {
            return;
        }

        $filePath = $this->basePath . '/app/Config/' . $group . '.php';

        if (file_exists($filePath)) {
            $data = require $filePath;

            if (!is_array($data)) {
                throw new InvalidArgumentException(
                    "Config file [{$filePath}] must return an array."
                );
            }

            $this->items[$group] = $data;
        }

        // Also try generated config (written by installer)
        $generatedPath = $this->basePath . '/app/Config/generated.php';
        if (file_exists($generatedPath) && !isset($this->loaded['__generated__'])) {
            $generated = require $generatedPath;
            if (is_array($generated)) {
                $this->items = array_replace_recursive($this->items, $generated);
            }
            $this->loaded['__generated__'] = true;
        }

        $this->loaded[$group] = true;
    }
}
