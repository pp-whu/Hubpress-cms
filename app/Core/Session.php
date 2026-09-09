<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * Session
 *
 * Manages PHP sessions with secure configuration:
 * - HttpOnly & SameSite cookie flags
 * - Session fixation protection (ID regeneration on login)
 * - Flash messages (one-time read)
 * - CSRF token storage
 *
 * @package HuberCMS\Core
 */
final class Session
{
    private bool $started = false;

    public function __construct(private readonly Config $config)
    {
        $this->configure();
    }

    // =========================================================
    // Lifecycle
    // =========================================================

    /**
     * Starts the session if not already active.
     */
    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            $this->rotateFlash();
            return;
        }

        if (headers_sent($file, $line)) {
            error_log(sprintf(
                'HuberCMS: Session::start() skipped — headers already sent in %s:%d',
                $file,
                $line
            ));
            return; // Do NOT set $this->started = true; caller can retry later
        }

        $result = session_start();

        if ($result) {
            $this->started = true;
            $this->rotateFlash();
        } else {
            error_log('HuberCMS: session_start() returned false — check session save path permissions.');
        }
    }

    /**
     * Regenerates the session ID (call on login/logout to prevent fixation).
     */
    public function regenerate(bool $deleteOld = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOld);
        }
    }

    /**
     * Destroys the session completely.
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $this->started = false;
    }

    // =========================================================
    // Data access
    // =========================================================

    /**
     * Gets a session value by key (dot-notation not supported here).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Sets a session value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    /**
     * Checks whether a session key exists.
     */
    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    /**
     * Removes a session value.
     */
    public function forget(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    /**
     * Returns all session data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $this->start();
        return $_SESSION ?? [];
    }

    // =========================================================
    // Flash messages
    // =========================================================

    /**
     * Stores a flash message — available only on the NEXT request.
     */
    public function flash(string $key, mixed $value): void
    {
        $this->start();
        $_SESSION['_flash']['new'][$key] = $value;
    }

    /**
     * Retrieves a flash message from the current request.
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION['_flash']['current'][$key] ?? $default;
    }

    /**
     * Checks if a flash message exists for this request.
     */
    public function hasFlash(string $key): bool
    {
        $this->start();
        return isset($_SESSION['_flash']['current'][$key]);
    }

    // =========================================================
    // CSRF
    // =========================================================

    /**
     * Returns the CSRF token, generating one if it doesn't exist.
     */
    public function csrfToken(): string
    {
        $this->start();

        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Validates a CSRF token using a timing-safe comparison.
     */
    public function validateCsrf(string $token): bool
    {
        $this->start();
        $stored = $_SESSION['_csrf_token'] ?? '';
        return hash_equals($stored, $token);
    }

    /**
     * Regenerates the CSRF token.
     */
    public function regenerateCsrf(): void
    {
        $this->start();
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Applies secure session ini settings before session_start().
     */
    private function configure(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $cfg = $this->config->all('session');

        // Determine session save path with fallback to system temp dir
        $savePath = $cfg['save_path'] ?? (STORAGE_PATH . '/Sessions');

        if (!is_dir($savePath)) {
            if (!@mkdir($savePath, 0755, true) && !is_dir($savePath)) {
                // Cannot create custom path — fall back to system temp dir
                $savePath = sys_get_temp_dir();
            }
        }

        // Verify the path is actually writable
        if (!is_writable($savePath)) {
            $savePath = sys_get_temp_dir();
        }

        ini_set('session.save_path',      $savePath);
        ini_set('session.name',           $cfg['name'] ?? 'hubercms_session');
        ini_set('session.gc_maxlifetime', (string) ($cfg['lifetime'] ?? 7200));
        ini_set('session.cookie_lifetime', '0');
        ini_set('session.cookie_path',    $cfg['path'] ?? '/');
        ini_set('session.cookie_secure',  ($cfg['secure'] ?? false) ? '1' : '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', $cfg['samesite'] ?? 'Lax');
        ini_set('session.use_strict_mode',  '1');
        ini_set('session.use_only_cookies', '1');
    }

    /**
     * Promotes "new" flash messages to "current" and clears old ones.
     */
    private function rotateFlash(): void
    {
        $_SESSION['_flash']['current'] = $_SESSION['_flash']['new'] ?? [];
        $_SESSION['_flash']['new'] = [];
    }
}
