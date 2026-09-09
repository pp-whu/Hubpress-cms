<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Request;
use HuberCMS\Core\Response;

/**
 * MiddlewareInterface
 *
 * Contract that all middleware classes must implement.
 * The $next callable receives the Request and returns a Response.
 *
 * @package HuberCMS\Middleware
 */
interface MiddlewareInterface
{
    /**
     * Handles an incoming request, optionally short-circuiting the pipeline.
     *
     * @param callable(): Response $next
     */
    public function handle(Request $request, callable $next): Response;
}
