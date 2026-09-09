<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use RuntimeException;
use Throwable;

/**
 * ContainerException
 *
 * Thrown when the DI Container cannot resolve a dependency.
 *
 * @package HuberCMS\Core
 */
final class ContainerException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
