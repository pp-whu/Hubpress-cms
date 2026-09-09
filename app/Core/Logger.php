<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

/**
 * Logger
 *
 * Wraps Monolog with channel-based logging.
 * Log files rotate daily and are stored in app/Storage/Logs/.
 *
 * Channels: app, auth, api, plugins, system
 *
 * Usage:
 *   $logger->info('User logged in', ['user_id' => 5]);
 *   $logger->channel('auth')->warning('Failed login attempt', ['ip' => '...']);
 *
 * @package HuberCMS\Core
 */
final class Logger
{
    /** @var array<string, MonologLogger> Resolved channel instances */
    private array $channels = [];

    private string $logPath;
    private Level  $defaultLevel;

    public function __construct(private readonly Config $config)
    {
        $this->logPath = STORAGE_PATH . '/Logs';
        $this->defaultLevel = $this->resolveLevel(
            $config->get('app.log_level', 'debug')
        );

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    // =========================================================
    // PSR-3 methods
    // =========================================================

    public function debug(string $message, array $context = []): void
    {
        $this->channel('app')->debug($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->channel('app')->info($message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->channel('app')->notice($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->channel('app')->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->channel('app')->error($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->channel('app')->critical($message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->channel('app')->alert($message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->channel('app')->emergency($message, $context);
    }

    // =========================================================
    // Channel access
    // =========================================================

    /**
     * Returns a Monolog logger for the given channel.
     * Creates and caches it on first access.
     */
    public function channel(string $name = 'app'): MonologLogger
    {
        if (!isset($this->channels[$name])) {
            $this->channels[$name] = $this->createChannel($name);
        }

        return $this->channels[$name];
    }

    // =========================================================
    // Private helpers
    // =========================================================

    private function createChannel(string $name): MonologLogger
    {
        $logger = new MonologLogger($name);

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );

        // Rotating file handler — keeps 30 days of logs
        $handler = new RotatingFileHandler(
            filename: "{$this->logPath}/{$name}.log",
            maxFiles: 30,
            level: $this->defaultLevel,
            bubble: true,
            filePermission: 0644
        );
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        // In debug mode also write to stderr
        if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $stderrHandler = new StreamHandler('php://stderr', Level::Debug);
            $stderrHandler->setFormatter($formatter);
            $logger->pushHandler($stderrHandler);
        }

        return $logger;
    }

    private function resolveLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug'     => Level::Debug,
            'info'      => Level::Info,
            'notice'    => Level::Notice,
            'warning'   => Level::Warning,
            'error'     => Level::Error,
            'critical'  => Level::Critical,
            'alert'     => Level::Alert,
            'emergency' => Level::Emergency,
            default     => Level::Debug,
        };
    }
}
