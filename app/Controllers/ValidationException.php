<?php

declare(strict_types=1);

namespace HuberCMS\Controllers;

use RuntimeException;

/**
 * ValidationException
 *
 * Thrown by Controller::validate() when input fails validation.
 * Contains the error map so the router/controller layer can redirect.
 *
 * @package HuberCMS\Controllers
 */
final class ValidationException extends RuntimeException
{
    /** @param array<string, string[]> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Validation failed.');
    }

    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
