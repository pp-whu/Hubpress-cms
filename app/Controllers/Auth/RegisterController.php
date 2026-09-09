<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Auth;

use HuberCMS\Controllers\Controller;
use HuberCMS\Controllers\ValidationException;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Core\Database;
use HuberCMS\Services\AuthService;
use HuberCMS\Models\User;
use HuberCMS\Enums\UserRole;

/**
 * RegisterController
 *
 * Handles new user registration.
 * Can be disabled in settings (registration_open = false).
 *
 * @package HuberCMS\Controllers\Auth
 */
final class RegisterController extends Controller
{
    public function __construct(
        Session                       $session,
        private readonly AuthService $auth,
        private readonly Database    $db
    ) {
        parent::__construct($session);
    }

    public function showForm(Request $request): Response
    {
        if (!$this->isRegistrationOpen()) {
            return $this->view('auth.register-closed', ['title' => 'Registrierung geschlossen']);
        }

        return $this->view('auth.register', ['title' => 'Registrieren']);
    }

    public function register(Request $request): Response
    {
        if (!$this->isRegistrationOpen()) {
            return $this->redirect('/login');
        }

        try {
            $this->validate($request, [
                'username'              => 'required|alphanumeric|minlength:3|maxlength:50',
                'email'                 => 'required|email|maxlength:255',
                'password'              => 'required|minlength:8|maxlength:255',
                'password_confirmation' => 'required|confirmed',
            ]);
        } catch (ValidationException) {
            return $this->redirect('/register');
        }

        // Check uniqueness
        $existing = User::findByEmail((string) $request->post('email'));
        if ($existing !== null) {
            $this->flashError('Diese E-Mail-Adresse ist bereits registriert.');
            return $this->redirect('/register');
        }

        $user = User::create([
            'username' => htmlspecialchars((string) $request->post('username'), ENT_QUOTES),
            'email'    => strtolower(trim((string) $request->post('email'))),
            'password' => $this->auth->hashPassword((string) $request->post('password')),
            'role'     => UserRole::Subscriber->value,
            'is_active' => 1,
        ]);

        $this->auth->loginUser($user);
        $this->flashSuccess('Willkommen! Dein Konto wurde erfolgreich erstellt.');
        return $this->redirect('/');
    }

    private function isRegistrationOpen(): bool
    {
        try {
            $p = $this->db->prefix();
            $row = $this->db->selectOne(
                "SELECT value FROM `{$p}settings` WHERE `key` = 'registration_open' LIMIT 1"
            );
            return ($row['value'] ?? '1') === '1';
        } catch (\Throwable) {
            return true;
        }
    }
}
