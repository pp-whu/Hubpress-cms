<?php

declare(strict_types=1);

namespace HuberCMS\Controllers;

use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\View;
use HuberCMS\Core\Session;
use HuberCMS\Core\Validation;

/**
 * Controller (Abstract Base)
 *
 * Provides shared helpers for all HuberCMS controllers:
 * - view() — renders a template
 * - json() — returns a JSON response
 * - redirect() — redirects to a URL
 * - validate() — validates request input
 * - flashSuccess() / flashError() — session flash messages
 *
 * @package HuberCMS\Controllers
 */
abstract class Controller
{
    public function __construct(protected readonly Session $session)
    {
    }

    // =========================================================
    // Response helpers
    // =========================================================

    /**
     * Renders a template and returns an HTML Response.
     *
     * @param array<string, mixed> $data
     */
    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        return View::make($template, $data, $status);
    }

    /**
     * Returns a JSON Response.
     *
     * @param mixed $data
     */
    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    /**
     * Returns a redirect Response.
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    /**
     * Redirects back to the previous page (uses Referer header).
     */
    protected function back(Request $request, string $fallback = '/'): Response
    {
        $referer = $request->header('Referer') ?? $fallback;
        // Security: only allow same-origin referers
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        if (!str_starts_with($referer, $appUrl)) {
            $referer = $fallback;
        }
        return $this->redirect($referer);
    }

    // =========================================================
    // Validation
    // =========================================================

    /**
     * Validates request data. Redirects back with errors on failure.
     *
     * @param array<string, string|string[]> $rules
     * @param array<string, mixed>           $data
     * @throws \RuntimeException If validation fails (caller can catch)
     */
    protected function validate(
        Request $request,
        array $rules,
        array $data = []
    ): Validation {
        if (empty($data)) {
            $data = $request->all();
        }

        $v = new Validation($rules);
        $v->validate($data);

        if ($v->fails()) {
            // Store errors and old input in session for re-display
            $this->session->flash('validation_errors', $v->errors());
            $this->session->flash('old_input', $data);

            throw new ValidationException($v->errors());
        }

        return $v;
    }

    // =========================================================
    // Flash messages
    // =========================================================

    protected function flashSuccess(string $message): void
    {
        $this->session->flash('success', $message);
    }

    protected function flashError(string $message): void
    {
        $this->session->flash('error', $message);
    }

    protected function flashWarning(string $message): void
    {
        $this->session->flash('warning', $message);
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * Returns old input value (from flash) — used to repopulate forms.
     */
    protected function old(string $key, mixed $default = ''): mixed
    {
        $old = $this->session->getFlash('old_input') ?? [];
        return $old[$key] ?? $default;
    }
}
