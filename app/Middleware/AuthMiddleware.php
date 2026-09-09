<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;

/**
 * AuthMiddleware
 *
 * Protects routes that require an authenticated user.
 * Redirects to login page or returns 401 JSON for API requests.
 *
 * @package HuberCMS\Middleware
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $this->session->get('auth_user');

        if ($user === null) {
            if ($request->wantsJson() || $request->isAjax()) {
                return Response::json(['error' => 'Unauthenticated.'], 401);
            }

            return Response::redirect('/login?redirect=' . urlencode($request->uri()));
        }

        return $next();
    }
}
