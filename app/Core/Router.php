<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use HuberCMS\Middleware\MiddlewareInterface;

/**
 * Router
 *
 * Registers and dispatches HTTP routes. Supports:
 * - Named routes with {parameter} placeholders
 * - Optional parameters {param?}
 * - Route groups with prefix and shared middleware
 * - Global and per-route middleware
 * - Controller@method syntax
 *
 * @package HuberCMS\Core
 */
final class Router
{
    /** @var array<int, array{method:string,pattern:string,handler:mixed,middleware:array,name:string|null}> */
    private array $routes = [];

    /** @var array<string, string> Named route map */
    private array $namedRoutes = [];

    /** @var MiddlewareInterface[] Global middleware applied to all routes */
    private array $globalMiddleware = [];

    /** @var array{prefix:string,middleware:array}[] Group stack */
    private array $groupStack = [];

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * Returns the DI container (used in route files to resolve services).
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    // =========================================================
    // Route registration
    // =========================================================

    public function get(string $pattern, mixed $handler, ?string $name = null): self
    {
        return $this->addRoute('GET', $pattern, $handler, $name);
    }

    public function post(string $pattern, mixed $handler, ?string $name = null): self
    {
        return $this->addRoute('POST', $pattern, $handler, $name);
    }

    public function put(string $pattern, mixed $handler, ?string $name = null): self
    {
        return $this->addRoute('PUT', $pattern, $handler, $name);
    }

    public function patch(string $pattern, mixed $handler, ?string $name = null): self
    {
        return $this->addRoute('PATCH', $pattern, $handler, $name);
    }

    public function delete(string $pattern, mixed $handler, ?string $name = null): self
    {
        return $this->addRoute('DELETE', $pattern, $handler, $name);
    }

    /**
     * Defines a route group with a shared prefix and optional middleware.
     *
     * @param array{prefix?:string,middleware?:array} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = [
            'prefix'     => $attributes['prefix'] ?? '',
            'middleware' => $attributes['middleware'] ?? [],
        ];

        $callback($this);

        array_pop($this->groupStack);
    }

    /**
     * Adds a global middleware applied to all routes.
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    // =========================================================
    // Dispatch
    // =========================================================

    /**
     * Matches the request to a registered route and calls its handler.
     * Returns a Response — or a 404/405 response if no match is found.
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri    = $request->uri();

        $methodMatched = false;

        foreach ($this->routes as $route) {
            $regex = $this->buildRegex($route['pattern']);

            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            // URI matched — check method
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                $methodMatched = true;
                continue;
            }

            // Extract named capture groups as route params
            $params = array_filter(
                $matches,
                fn($key) => !is_int($key),
                ARRAY_FILTER_USE_KEY
            );

            // Run middleware pipeline
            $middleware = array_merge($this->globalMiddleware, $route['middleware']);
            $response = $this->runMiddleware($middleware, $request, function () use ($route, $request, $params) {
                return $this->callHandler($route['handler'], $request, $params);
            });

            return $response;
        }

        if ($methodMatched) {
            return Response::html('<h1>405 — Method Not Allowed</h1>', 405);
        }

        return Response::html('<h1>404 — Page Not Found</h1>', 404);
    }

    /**
     * Generates a URL for a named route, replacing parameters.
     *
     * @param array<string, string> $params
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route [{$name}] not found.");
        }

        $pattern = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $pattern = preg_replace(
                '/\{' . preg_quote($key, '/') . '\??\}/',
                (string) $value,
                $pattern
            );
        }

        // Remove remaining optional params
        $pattern = preg_replace('/\/\{[^}]+\?\}/', '', $pattern);

        return $pattern;
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Registers a route with the current group context applied.
     */
    private function addRoute(string $method, string $pattern, mixed $handler, ?string $name): self
    {
        $prefix = '';
        $middleware = [];

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'];
            $middleware = array_merge($middleware, $group['middleware']);
        }

        $fullPattern = '/' . ltrim($prefix . $pattern, '/');

        $route = [
            'method'     => $method,
            'pattern'    => $fullPattern,
            'handler'    => $handler,
            'middleware' => $middleware,
            'name'       => $name,
        ];

        $this->routes[] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $fullPattern;
        }

        return $this;
    }

    /**
     * Converts a route pattern with {param} placeholders into a regex.
     */
    private function buildRegex(string $pattern): string
    {
        // Optional parameters: {param?} → (?P<param>[^/]+)?
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^/]+)?', $pattern);
        // Required parameters: {param} → (?P<param>[^/]+)
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);

        return '#^' . rtrim($pattern, '/') . '/?$#';
    }

    /**
     * Runs the middleware pipeline and calls the final handler.
     *
     * @param MiddlewareInterface[] $middleware
     */
    private function runMiddleware(array $middleware, Request $request, callable $handler): Response
    {
        if (empty($middleware)) {
            return $handler();
        }

        $next = $handler;

        foreach (array_reverse($middleware) as $mw) {
            $currentNext = $next;
            $next = fn() => $mw->handle($request, $currentNext);
        }

        return $next();
    }

    /**
     * Calls a route handler — either a Closure or 'Controller@method' string.
     *
     * @param array<string, string> $params
     */
    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        if ($handler instanceof \Closure) {
            return $handler($request, ...array_values($params));
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);

            if (!class_exists($class)) {
                $class = 'HuberCMS\\Controllers\\' . $class;
            }

            $controller = $this->container->make($class);

            return $controller->$method($request, ...array_values($params));
        }

        throw new \InvalidArgumentException('Invalid route handler.');
    }
}
