<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Container;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Services\JwtService;

/**
 * ApiAuthMiddleware
 *
 * Validates a Bearer JWT token or an API key for API routes.
 * Sets the authenticated user on the request context.
 *
 * @package HuberCMS\Middleware
 */
final class ApiAuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Container $container)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $authHeader = $request->header('Authorization') ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            // Try API key header
            $apiKey = $request->header('X-Api-Key') ?? '';
            if (!empty($apiKey) && $this->validateApiKey($apiKey)) {
                return $next();
            }

            return Response::json(['error' => 'Unauthenticated. Bearer token or X-Api-Key required.'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            /** @var JwtService $jwt */
            $jwt = $this->container->make(JwtService::class);
            $payload = $jwt->decode($token);

            $userId = (int) ($payload->sub ?? 0);
            if (!$this->isActiveUser($userId)) {
                return Response::json(['error' => 'Invalid or expired token.'], 401);
            }

            // Token valid — could store payload in a request attribute bag if needed
            return $next();
        } catch (\Throwable) {
            return Response::json(['error' => 'Invalid or expired token.'], 401);
        }
    }

    private function validateApiKey(string $key): bool
    {
        try {
            $db = $this->container->make(\HuberCMS\Core\Database::class);
            $prefix = $db->prefix();
            $hash = hash('sha256', $key);

            $row = $db->selectOne(
                "SELECT ak.id FROM `{$prefix}api_keys` ak
                 INNER JOIN `{$prefix}users` u ON u.id = ak.user_id
                 WHERE ak.key_hash = ? AND ak.is_active = 1 AND u.is_active = 1
                 AND (ak.expires_at IS NULL OR ak.expires_at > NOW())",
                [$hash]
            );

            if ($row !== null) {
                // Update last_used_at (fire and forget)
                $db->statement(
                    "UPDATE `{$prefix}api_keys` SET last_used_at = NOW() WHERE id = ?",
                    [$row['id']]
                );
                return true;
            }
        } catch (\Throwable) {
        }

        return false;
    }

    private function isActiveUser(int $userId): bool
    {
        if ($userId < 1) {
            return false;
        }

        try {
            $db = $this->container->make(\HuberCMS\Core\Database::class);
            $prefix = $db->prefix();
            $row = $db->selectOne(
                "SELECT id FROM `{$prefix}users` WHERE id = ? AND is_active = 1",
                [$userId]
            );

            return $row !== null;
        } catch (\Throwable) {
            return false;
        }
    }
}
