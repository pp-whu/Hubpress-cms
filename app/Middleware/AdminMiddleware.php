<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;

/**
 * AdminMiddleware
 *
 * Ensures the authenticated user has an admin or super-admin role.
 * Must be used after AuthMiddleware in the stack.
 *
 * @package HuberCMS\Middleware
 */
final class AdminMiddleware implements MiddlewareInterface
{
    private const ADMIN_ROLES = ['admin', 'super_admin', 'editor'];

    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        /** @var array<string, mixed>|null $user */
        $user = $this->session->get('auth_user');

        if ($user === null) {
            return Response::redirect('/login');
        }

        $role = $user['role'] ?? '';

        if (!in_array($role, self::ADMIN_ROLES, true)) {
            if ($request->wantsJson() || $request->isAjax()) {
                return Response::json(['error' => 'Forbidden. Insufficient permissions.'], 403);
            }

            return Response::html('<h1>403 — Forbidden</h1><p>You do not have permission to access this area.</p>', 403);
        }

        return $next();
    }
}
