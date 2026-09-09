<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * Cache
 *
 * Multi-driver cache system supporting: file, redis, apcu, array (testing).
 * All keys are automatically prefixed.
 *
 * @package HuberCMS\Core
 */
final class Cache
{
    private string $driver;
    private string $prefix;
    private int    $defaultTtl;
    private string $filePath;

    /** @var array<string, mixed> In-memory array cache (driver=array) */
    private array $arrayStore = [];

    /** @var \Redis|null */
    private ?\Redis $redis = null;

    public function __construct(private readonly Config $config)
    {
        $this->driver     = $config->get('cache.driver', 'file');
        $this->prefix     = $config->get('cache.prefix', 'hubercms_');
        $this->defaultTtl = (int) $config->get('cache.ttl', 3600);
        $this->filePath   = $config->get('cache.stores.file.path', STORAGE_PATH . '/Cache');

        if (!is_dir($this->filePath)) {
            mkdir($this->filePath, 0755, true);
        }
    }

    // =========================================================
    // Public API
    // =========================================================

    /**
     * Stores a value in the cache.
     *
     * @param mixed $value
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return match ($this->driver) {
            'redis' => $this->redisSet($key, $value, $ttl),
            'apcu'  => apcu_store($this->prefixed($key), serialize($value), $ttl),
            'array' => $this->arraySet($key, $value, $ttl),
            default => $this->fileSet($key, $value, $ttl),
        };
    }

    /**
     * Returns a cached value or the default if not found / expired.
     *
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return match ($this->driver) {
            'redis' => $this->redisGet($key, $default),
            'apcu'  => $this->apcuGet($key, $default),
            'array' => $this->arrayGet($key, $default),
            default => $this->fileGet($key, $default),
        };
    }

    /**
     * Checks whether a cache key exists and is not expired.
     */
    public function has(string $key): bool
    {
        return $this->get($key, '__MISS__') !== '__MISS__';
    }

    /**
     * Removes a value from the cache.
     */
    public function forget(string $key): bool
    {
        return match ($this->driver) {
            'redis' => (bool) $this->getRedis()->del($this->prefixed($key)),
            'apcu'  => apcu_delete($this->prefixed($key)),
            'array' => $this->arrayForget($key),
            default => $this->fileForget($key),
        };
    }

    /**
     * Clears the entire cache store.
     */
    public function flush(): void
    {
        match ($this->driver) {
            'redis' => $this->redisFlush(),
            'apcu'  => apcu_clear_cache(),
            'array' => $this->arrayStore = [],
            default => $this->fileFlush(),
        };
    }

    /**
     * Gets a value or stores the result of the callback if missing.
     *
     * @param mixed $default
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    // =========================================================
    // File driver
    // =========================================================

    private function fileSet(string $key, mixed $value, int $ttl): bool
    {
        $payload = ['expires' => time() + $ttl, 'data' => $value];
        return (bool) file_put_contents(
            $this->filePath($key),
            serialize($payload),
            LOCK_EX
        );
    }

    private function fileGet(string $key, mixed $default): mixed
    {
        $path = $this->filePath($key);

        if (!file_exists($path)) {
            return $default;
        }

        $payload = $this->safeUnserialize((string) file_get_contents($path));

        if (!is_array($payload) || !isset($payload['expires']) || $payload['expires'] < time()) {
            @unlink($path);
            return $default;
        }

        return $payload['data'];
    }

    private function fileForget(string $key): bool
    {
        $path = $this->filePath($key);
        return file_exists($path) ? unlink($path) : true;
    }

    private function fileFlush(): void
    {
        foreach (glob($this->filePath . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }
    }

    private function filePath(string $key): string
    {
        return $this->filePath . '/' . md5($this->prefixed($key)) . '.cache';
    }

    // =========================================================
    // APCu driver
    // =========================================================

    private function apcuGet(string $key, mixed $default): mixed
    {
        $success = false;
        $value = apcu_fetch($this->prefixed($key), $success);
        if (!$success || !is_string($value)) {
            return $default;
        }
        return $this->safeUnserialize($value, $default);
    }

    // =========================================================
    // Redis driver
    // =========================================================

    private function redisSet(string $key, mixed $value, int $ttl): bool
    {
        return (bool) $this->getRedis()->setex($this->prefixed($key), $ttl, serialize($value));
    }

    private function redisGet(string $key, mixed $default): mixed
    {
        $value = $this->getRedis()->get($this->prefixed($key));

        if ($value === false) {
            return $default;
        }

        return $this->safeUnserialize((string) $value, $default);
    }

    private function redisFlush(): void
    {
        $keys = $this->getRedis()->keys($this->prefix . '*');
        if (!empty($keys)) {
            $this->getRedis()->del($keys);
        }
    }

    private function getRedis(): \Redis
    {
        if ($this->redis === null) {
            $cfg = $this->config->all('cache')['stores']['redis'] ?? [];
            $this->redis = new \Redis();
            $this->redis->connect($cfg['host'] ?? '127.0.0.1', $cfg['port'] ?? 6379);
            if (!empty($cfg['password'])) {
                $this->redis->auth($cfg['password']);
            }
        }
        return $this->redis;
    }

    // =========================================================
    // Array driver (testing)
    // =========================================================

    private function arraySet(string $key, mixed $value, int $ttl): bool
    {
        $this->arrayStore[$this->prefixed($key)] = ['expires' => time() + $ttl, 'data' => $value];
        return true;
    }

    private function arrayGet(string $key, mixed $default): mixed
    {
        $entry = $this->arrayStore[$this->prefixed($key)] ?? null;
        if ($entry === null || $entry['expires'] < time()) {
            return $default;
        }
        return $entry['data'];
    }

    private function arrayForget(string $key): bool
    {
        unset($this->arrayStore[$this->prefixed($key)]);
        return true;
    }

    // =========================================================
    // Helpers
    // =========================================================

    private function prefixed(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * Deserializes cached data while preventing object injection via PHP object payloads.
     *
     * @param mixed $default
     * @return mixed
     */
    private function safeUnserialize(string $value, mixed $default = null): mixed
    {
        try {
            $data = unserialize($value, ['allowed_classes' => false]);
        } catch (\Throwable) {
            return $default;
        }

        return $data === false && $value !== 'b:0;' ? $default : $data;
    }
}
