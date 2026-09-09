<?php

declare(strict_types=1);

namespace HuberCMS\Services;

use HuberCMS\Core\Config;
use HuberCMS\Core\Jwt;
use stdClass;

/**
 * JwtService
 *
 * Generates and validates JSON Web Tokens using the built-in HuberCMS\Core\Jwt
 * class (no external dependency). Tokens are signed with HS256 (HMAC-SHA256).
 *
 * @package HuberCMS\Services
 */
final class JwtService
{
    private string $secret;
    private int    $ttl;

    public function __construct(Config $config)
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? $config->get('app.key', '');
        $this->ttl    = (int) ($_ENV['JWT_TTL'] ?? 3600);

        if (empty($this->secret)) {
            throw new \RuntimeException(
                'JWT_SECRET is not configured. Set it in .env or run the installer.'
            );
        }
    }

    // =========================================================
    // Token generation
    // =========================================================

    /**
     * Creates a signed JWT for the given user data.
     *
     * @param array<string, mixed> $user
     */
    public function encode(array $user): string
    {
        $now = time();

        $payload = [
            'iss'  => $_ENV['APP_URL'] ?? 'hubercms',
            'iat'  => $now,
            'nbf'  => $now,
            'exp'  => $now + $this->ttl,
            'sub'  => (string) ($user['id'] ?? ''),
            'user' => [
                'id'       => $user['id'] ?? null,
                'email'    => $user['email'] ?? '',
                'username' => $user['username'] ?? '',
                'role'     => $user['role'] ?? '',
            ],
        ];

        return Jwt::encode($payload, $this->secret);
    }

    // =========================================================
    // Token decoding & validation
    // =========================================================

    /**
     * Decodes and validates a JWT. Throws on failure.
     *
     * @return stdClass Decoded payload
     * @throws \RuntimeException On invalid format, bad signature or expiration
     */
    public function decode(string $token): stdClass
    {
        return Jwt::decode($token, $this->secret);
    }

    /**
     * Returns the remaining TTL (seconds) for a token, or 0 if expired/invalid.
     */
    public function remainingTtl(string $token): int
    {
        try {
            $payload = $this->decode($token);
            return max(0, (int) $payload->exp - time());
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Generates a new token with the same user data but a refreshed expiry.
     */
    public function refresh(string $token): string
    {
        $payload = $this->decode($token);
        $user    = (array) $payload->user;
        return $this->encode($user);
    }
}
