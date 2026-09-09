<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * Request
 *
 * Encapsulates the current HTTP request. Provides safe accessors
 * for query parameters, POST body, JSON payload, headers, uploaded
 * files and server variables.
 *
 * All user input returned by this class is raw — sanitization and
 * validation are the responsibility of the receiving layer.
 *
 * @package HuberCMS\Core
 */
final class Request
{
    /** @var array<string, string> */
    private array $query;

    /** @var array<string, mixed> */
    private array $post;

    /** @var array<string, mixed> */
    private array $files;

    /** @var array<string, string> */
    private array $server;

    /** @var array<string, string> */
    private array $headers;

    /** @var array<string, mixed>|null Decoded JSON body (lazy) */
    private ?array $jsonBody = null;

    /** @var string Raw body content (lazy) */
    private ?string $rawBody = null;

    public function __construct(
        array $query = [],
        array $post = [],
        array $files = [],
        array $server = []
    ) {
        $this->query   = $query;
        $this->post    = $post;
        $this->files   = $files;
        $this->server  = $server;
        $this->headers = $this->parseHeaders($server);
    }

    /**
     * Creates a Request from PHP superglobals.
     */
    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER);
    }

    // =========================================================
    // Method & URI
    // =========================================================

    /**
     * Returns the HTTP method in uppercase (GET, POST, PUT, DELETE, PATCH).
     */
    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

        // Allow method spoofing via _method field (for HTML forms)
        if ($method === 'POST') {
            $spoofed = strtoupper($this->post('_method', ''));
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofed;
            }
        }

        return $method;
    }

    /**
     * Returns the URI path without query string.
     */
    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        return '/' . ltrim(rawurldecode($path), '/');
    }

    /**
     * Returns the full query string.
     */
    public function queryString(): string
    {
        return $this->server['QUERY_STRING'] ?? '';
    }

    /**
     * Checks whether the request method matches.
     */
    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * Checks if this is an AJAX (XMLHttpRequest) request.
     */
    public function isAjax(): bool
    {
        return ($this->header('X-Requested-With') ?? '') === 'XMLHttpRequest';
    }

    /**
     * Checks if the client accepts JSON.
     */
    public function wantsJson(): bool
    {
        $accept = $this->header('Accept') ?? '';
        return str_contains($accept, 'application/json');
    }

    /**
     * Checks if this request is served over HTTPS.
     */
    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') !== ''
            && strtolower($this->server['HTTPS']) !== 'off';
    }

    // =========================================================
    // Input
    // =========================================================

    /**
     * Returns a GET parameter (or all if $key is null).
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Returns a POST parameter (or all if $key is null).
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Returns input from POST or JSON body.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key]
            ?? $this->json($key)
            ?? $this->query[$key]
            ?? $default;
    }

    /**
     * Returns all POST + JSON input merged.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->jsonBody ?? []);
    }

    /**
     * Returns only the specified keys from input.
     *
     * @param string[] $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Returns a value from the decoded JSON body.
     */
    public function json(string $key, mixed $default = null): mixed
    {
        if ($this->jsonBody === null) {
            $this->jsonBody = $this->parseJsonBody();
        }

        return $this->jsonBody[$key] ?? $default;
    }

    /**
     * Returns an uploaded file array by name.
     *
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    // =========================================================
    // Headers & Server
    // =========================================================

    /**
     * Returns a request header value (case-insensitive).
     */
    public function header(string $name): ?string
    {
        $normalized = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$normalized] ?? null;
    }

    /**
     * Returns the client's IP address.
     */
    public function ip(): string
    {
        // Honour reverse-proxy headers only if explicitly trusted
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($this->server[$key])) {
                return explode(',', $this->server[$key])[0];
            }
        }

        return '0.0.0.0';
    }

    /**
     * Returns the User-Agent string.
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Parses HTTP_ server variables into normalized header map.
     *
     * @param array<string, string> $server
     * @return array<string, string>
     */
    private function parseHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = substr($key, 5); // strip HTTP_
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * Decodes the raw request body as JSON.
     *
     * @return array<string, mixed>
     */
    private function parseJsonBody(): array
    {
        $contentType = $this->header('Content-Type') ?? '';

        if (!str_contains($contentType, 'application/json')) {
            return [];
        }

        if ($this->rawBody === null) {
            $this->rawBody = (string) file_get_contents('php://input');
        }

        if (empty($this->rawBody)) {
            return [];
        }

        $decoded = json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : [];
    }
}
