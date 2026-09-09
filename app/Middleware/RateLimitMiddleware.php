<?php

declare(strict_types=1);

namespace HuberCMS\Middleware;

use HuberCMS\Core\Config;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;

/**
 * RateLimitMiddleware
 *
 * Limits the number of requests per IP per time window using APCu or file-based counters.
 * OWASP: prevents brute-force and DoS attacks.
 *
 * @package HuberCMS\Middleware
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(Config $config)
    {
        $this->maxRequests   = (int) $config->get('app.security.rate_limit_max', 100);
        $this->windowSeconds = (int) $config->get('app.security.rate_limit_window', 60);
    }

    public function handle(Request $request, callable $next): Response
    {
        $ip  = $request->ip();
        $key = 'rate_limit_' . md5($ip);

        [$count, $blocked] = $this->checkLimit($key);

        if ($blocked) {
            $response = Response::json(
                ['error' => 'Too Many Requests. Please slow down.'],
                429
            );
            $response->setHeader('Retry-After', (string) $this->windowSeconds);
            $response->setHeader('X-RateLimit-Limit', (string) $this->maxRequests);
            $response->setHeader('X-RateLimit-Remaining', '0');
            return $response;
        }

        $response = $next();
        $response->setHeader('X-RateLimit-Limit', (string) $this->maxRequests);
        $response->setHeader('X-RateLimit-Remaining', (string) max(0, $this->maxRequests - $count));

        return $response;
    }

    /**
     * Checks and increments the rate limit counter.
     *
     * @return array{0:int, 1:bool} [current count, is blocked]
     */
    private function checkLimit(string $key): array
    {
        if (function_exists('apcu_fetch') && ini_get('apc.enabled')) {
            return $this->checkApcuLimit($key);
        }

        return $this->checkFileLimit($key);
    }

    /** @return array{0:int, 1:bool} */
    private function checkApcuLimit(string $key): array
    {
        $success = false;
        $count = (int) apcu_fetch($key, $success);

        if (!$success) {
            apcu_store($key, 1, $this->windowSeconds);
            return [1, false];
        }

        $count = (int) apcu_inc($key);
        return [$count, $count > $this->maxRequests];
    }

    /** @return array{0:int, 1:bool} */
    private function checkFileLimit(string $key): array
    {
        $file = sys_get_temp_dir() . '/hubercms_rl_' . $key . '.json';
        $now  = time();

        if (file_exists($file)) {
            $data = json_decode((string) file_get_contents($file), true) ?? [];
            $windowStart = $data['start'] ?? $now;
            $count = $data['count'] ?? 0;

            if ($now - $windowStart > $this->windowSeconds) {
                // Reset window
                $data = ['start' => $now, 'count' => 1];
                file_put_contents($file, json_encode($data), LOCK_EX);
                return [1, false];
            }

            $count++;
            $data['count'] = $count;
            file_put_contents($file, json_encode($data), LOCK_EX);
            return [$count, $count > $this->maxRequests];
        }

        file_put_contents($file, json_encode(['start' => $now, 'count' => 1]), LOCK_EX);
        return [1, false];
    }
}
