<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Auth;

use HuberCMS\Controllers\Controller;
use HuberCMS\Controllers\ValidationException;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Services\AuthService;

/**
 * LoginController
 *
 * Handles user login and logout.
 * OWASP: Credential enumeration prevention, brute-force protection via AuthService.
 *
 * @package HuberCMS\Controllers\Auth
 */
final class LoginController extends Controller
{
    public function __construct(
        Session                       $session,
        private readonly AuthService $auth
    ) {
        parent::__construct($session);
    }

    /**
     * Renders the login form.
     */
    public function showForm(Request $request): Response
    {
        if ($this->auth->check()) {
            return $this->redirect('/admin');
        }

        return $this->view('auth.login', [
            'title'       => 'Anmelden',
            'redirect'    => $request->query('redirect', '/admin'),
        ]);
    }

    /**
     * Processes login form submission.
     */
    public function login(Request $request): Response
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email|maxlength:255',
                'password' => 'required|minlength:1',
            ]);
        } catch (ValidationException $e) {
            $this->flashError('Bitte überprüfe deine Eingaben.');
            return $this->redirect('/login');
        }

        $result = $this->auth->attempt(
            (string) $request->post('email', ''),
            (string) $request->post('password', ''),
            (bool) $request->post('remember', false)
        );

        if (!$result['success']) {
            $this->flashError($result['error'] ?? 'Anmeldung fehlgeschlagen.');
            return $this->redirect('/login');
        }

        if ($result['requires_2fa'] ?? false) {
            return $this->redirect('/login/2fa');
        }

        $redirect = $this->sanitizeRedirect((string) $request->post('redirect', '/admin'));
        $this->flashSuccess('Willkommen zurück!');
        return $this->redirect($redirect);
    }

    /**
     * Logs the user out and destroys the session.
     */
    public function logout(Request $request): Response
    {
        $this->auth->logout();
        $this->flashSuccess('Du wurdest erfolgreich abgemeldet.');
        return $this->redirect('/login');
    }

    /**
     * Only allow same-origin redirects after login.
     */
    private function sanitizeRedirect(string $url): string
    {
        // Allow only relative paths starting with /
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        return '/admin';
    }
}
