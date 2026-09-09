<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;

/**
 * Restricts sensitive administration routes to administrator roles.
 */
final class AdminOnlyMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        /** @var array<string, mixed>|null $user */
        $user = $this->session->get('auth_user');
        $role = is_string($user['role'] ?? null) ? $user['role'] : '';

        if (!in_array($role, ['admin', 'super_admin'], true)) {
            if ($request->wantsJson() || $request->isAjax()) {
                return Response::json(['error' => 'Forbidden. Admin access required.'], 403);
            }

            return Response::html('<h1>403 — Forbidden</h1><p>You do not have permission to access this area.</p>', 403);
        }

        return $next();
    }
}