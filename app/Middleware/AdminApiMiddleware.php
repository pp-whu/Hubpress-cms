<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Container;
use HuberCMS\Core\Database;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Services\JwtService;

/**
 * AdminApiMiddleware
 *
 * Enforces admin-level access on selected API routes.
 */
final class AdminApiMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Container $container)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $this->resolveAuthenticatedUser($request);

        if ($user === null) {
            return Response::json(['error' => 'Unauthenticated.'], 401);
        }

        $role = is_string($user['role'] ?? null) ? strtolower($user['role']) : '';

        if (!in_array($role, ['admin', 'super_admin'], true)) {
            return Response::json(['error' => 'Forbidden. Admin access required.'], 403);
        }

        return $next();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveAuthenticatedUser(Request $request): ?array
    {
        $authHeader = $request->header('Authorization') ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            try {
                /** @var JwtService $jwt */
                $jwt = $this->container->make(JwtService::class);
                $payload = $jwt->decode(substr($authHeader, 7));

                return is_array($payload->user ?? null) ? $payload->user : null;
            } catch (\Throwable) {
                return null;
            }
        }

        $apiKey = $request->header('X-Api-Key') ?? '';
        if ($apiKey === '') {
            return null;
        }

        try {
            /** @var Database $db */
            $db = $this->container->make(Database::class);
            $prefix = $db->prefix();
            $hash = hash('sha256', $apiKey);

            $row = $db->selectOne(
                "SELECT u.role, u.id, u.email, u.username
                 FROM `{$prefix}api_keys` ak
                 INNER JOIN `{$prefix}users` u ON u.id = ak.user_id
                 WHERE ak.key_hash = ? AND ak.is_active = 1
                 AND (ak.expires_at IS NULL OR ak.expires_at > NOW())",
                [$hash]
            );

            if ($row === null) {
                return null;
            }

            return [
                'id' => (int) ($row['id'] ?? 0),
                'email' => (string) ($row['email'] ?? ''),
                'username' => (string) ($row['username'] ?? ''),
                'role' => (string) ($row['role'] ?? ''),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}