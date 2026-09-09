<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use Throwable;

/**
 * ExceptionHandler
 *
 * Handles uncaught exceptions and errors globally.
 * In debug mode renders full stack traces; in production shows
 * a generic error page and writes to the log.
 *
 * @package HuberCMS\Core
 */
final class ExceptionHandler
{
    /**
     * Handles a Throwable, renders an appropriate response and exits.
     */
    public static function handle(Throwable $e): void
    {
        $statusCode = self::resolveStatusCode($e);
        $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Log the error (best-effort)
        self::logError($e);

        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: text/html; charset=UTF-8');
        }

        if ($isDebug) {
            echo self::renderDebugPage($e, $statusCode);
        } else {
            echo self::renderProductionPage($statusCode);
        }

        exit(1);
    }

    private static function resolveStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof \InvalidArgumentException => 400,
            $e instanceof \RuntimeException         => 500,
            default                                 => 500,
        };
    }

    private static function logError(Throwable $e): void
    {
        $message = sprintf(
            '[%s] %s in %s:%d',
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        $logFile = STORAGE_PATH . '/Logs/error.log';

        if (is_writable(dirname($logFile))) {
            error_log($message . PHP_EOL . $e->getTraceAsString() . PHP_EOL, 3, $logFile);
        } else {
            error_log($message);
        }
    }

    private static function renderDebugPage(Throwable $e, int $status): string
    {
        $class   = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file    = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line    = $e->getLine();
        $trace   = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <title>HuberCMS — Error {$status}</title>
            <style>
                body { font-family: monospace; background: #1a1a2e; color: #e0e0e0; padding: 2rem; }
                h1   { color: #e94560; }
                .box { background: #16213e; padding: 1.5rem; border-radius: 8px; margin: 1rem 0; }
                pre  { overflow: auto; font-size: 0.85rem; color: #a8dadc; }
                .label { color: #a29bfe; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>&#9889; {$status} — {$class}</h1>
            <div class="box"><span class="label">Message:</span> {$message}</div>
            <div class="box"><span class="label">File:</span> {$file} <b>:{$line}</b></div>
            <div class="box"><span class="label">Stack trace:</span><pre>{$trace}</pre></div>
        </body>
        </html>
        HTML;
    }

    private static function renderProductionPage(int $status): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <title>HuberCMS — {$status}</title>
            <style>
                body { font-family: sans-serif; display: flex; align-items: center;
                       justify-content: center; min-height: 100vh; margin: 0;
                       background: #1a1a2e; color: #e0e0e0; text-align: center; }
                h1   { font-size: 4rem; color: #e94560; margin: 0; }
                p    { color: #a0a0b0; }
            </style>
        </head>
        <body>
            <div>
                <h1>{$status}</h1>
                <p>Ein unerwarteter Fehler ist aufgetreten.<br>Bitte versuche es später erneut.</p>
            </div>
        </body>
        </html>
        HTML;
    }
}
