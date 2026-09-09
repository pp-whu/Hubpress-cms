<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * Response
 *
 * Represents an outgoing HTTP response. Provides a fluent API
 * for setting status codes, headers and body content.
 * Supports HTML, JSON and redirect responses.
 *
 * @package HuberCMS\Core
 */
final class Response
{
    /** @var array<string, string> */
    private array $headers = [];

    private string $body = '';
    private int $statusCode = 200;

    /** @var array<int, string> Standard HTTP status texts */
    private const STATUS_TEXTS = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        419 => 'CSRF Token Mismatch',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];

    // =========================================================
    // Fluent setters
    // =========================================================

    /**
     * Sets the HTTP status code.
     */
    public function setStatus(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Sets a response header (replaces existing with same name).
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Sets the response body.
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    // =========================================================
    // Factory methods
    // =========================================================

    /**
     * Creates an HTML response.
     */
    public static function html(string $content, int $status = 200): self
    {
        $response = new self();
        $response->statusCode = $status;
        $response->body = $content;
        $response->headers['Content-Type'] = 'text/html; charset=UTF-8';
        return $response;
    }

    /**
     * Creates a JSON response.
     *
     * @param mixed $data
     */
    public static function json(mixed $data, int $status = 200): self
    {
        $response = new self();
        $response->statusCode = $status;
        $response->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $response->headers['Content-Type'] = 'application/json; charset=UTF-8';
        return $response;
    }

    /**
     * Creates a redirect response.
     */
    public static function redirect(string $url, int $status = 302): self
    {
        $sanitizedUrl = self::sanitizeRedirectTarget($url);
        $response = new self();
        $response->statusCode = $status;
        $response->headers['Location'] = $sanitizedUrl;
        $response->body = '';
        return $response;
    }

    /**
     * Ensures redirect targets are either relative application paths or
     * same-host absolute URLs to avoid open-redirect abuse.
     */
    private static function sanitizeRedirectTarget(string $url): string
    {
        $target = trim($url);

        if ($target === '') {
            return '/';
        }

        if (preg_match('/^(?:javascript|data|vbscript)\s*:/i', $target) === 1) {
            return '/';
        }

        if (str_starts_with($target, '//')) {
            return '/';
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//', $target) === 1) {
            $parsedUrl = parse_url($target);
            $requestedHost = strtolower((string) ($parsedUrl['host'] ?? ''));

            $allowedHost = strtolower(
                (string) ($_ENV['APP_URL'] ?? '')
            );
            $allowedHost = parse_url($allowedHost, PHP_URL_HOST) ?? $allowedHost;

            if ($requestedHost !== '' && $requestedHost !== $allowedHost) {
                return '/';
            }

            return $target;
        }

        if (preg_match('/^(?:\/|\.?\.?(?:\/|$)|[A-Za-z0-9_\-\\./?&=%#]+)$/', $target) !== 1) {
            return '/';
        }

        return $target;
    }

    /**
     * Creates a "No Content" response.
     */
    public static function noContent(): self
    {
        $response = new self();
        $response->statusCode = 204;
        return $response;
    }

    /**
     * Creates an error response.
     */
    public static function error(string $message, int $status = 500): self
    {
        return self::json(['error' => $message, 'status' => $status], $status);
    }

    // =========================================================
    // Output
    // =========================================================

    /**
     * Sends the response to the client.
     * Sets status code, headers, and outputs the body.
     */
    public function send(): void
    {
        if (!headers_sent()) {
            $statusText = self::STATUS_TEXTS[$this->statusCode] ?? 'Unknown';
            header(
                sprintf('HTTP/1.1 %d %s', $this->statusCode, $statusText),
                true,
                $this->statusCode
            );

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}", true);
            }
        }

        echo $this->body;
    }

    // =========================================================
    // Getters
    // =========================================================

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
