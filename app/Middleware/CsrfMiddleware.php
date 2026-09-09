<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;

/**
 * CsrfMiddleware
 *
 * Validates the CSRF token on all state-changing requests (POST, PUT, PATCH, DELETE).
 * Skips validation for the API prefix (JWT-protected) and for GET/HEAD/OPTIONS.
 *
 * @package HuberCMS\Middleware
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    /** Routes (URI prefixes) that are exempt from CSRF checks */
    private const EXEMPT_PREFIXES = ['/api/'];

    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next();
        }

        $token = $request->post('_token')
            ?? $request->header('X-CSRF-Token')
            ?? $request->header('X-XSRF-Token')
            ?? '';

        if (!$this->session->validateCsrf($token)) {
            if ($request->wantsJson() || $request->isAjax()) {
                return Response::json(['error' => 'CSRF token mismatch.'], 419);
            }

            return Response::html('<h1>419 — CSRF Token Mismatch</h1><p>Please go back and try again.</p>', 419);
        }

        return $next();
    }

    private function shouldSkip(Request $request): bool
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $uri = $request->uri();

        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
