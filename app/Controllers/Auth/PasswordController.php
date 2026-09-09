<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Auth;

use HuberCMS\Controllers\Controller;
use HuberCMS\Controllers\ValidationException;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Services\AuthService;
use HuberCMS\Services\MailService;

/**
 * PasswordController
 *
 * Handles password forgot and reset flows.
 * Uses time-limited tokens (1 hour) stored as SHA-256 hashes.
 *
 * @package HuberCMS\Controllers\Auth
 */
final class PasswordController extends Controller
{
    public function __construct(
        Session                       $session,
        private readonly AuthService $auth,
        private readonly MailService $mail
    ) {
        parent::__construct($session);
    }

    public function showForgot(Request $request): Response
    {
        return $this->view('auth.password-forgot', ['title' => 'Passwort vergessen']);
    }

    public function sendReset(Request $request): Response
    {
        try {
            $this->validate($request, ['email' => 'required|email']);
        } catch (ValidationException) {
            return $this->redirect('/password/forgot');
        }

        $email = strtolower(trim((string) $request->post('email')));
        $user  = \HuberCMS\Models\User::findByEmail($email);

        // Always show success to prevent email enumeration (OWASP)
        $this->flashSuccess('Falls ein Konto mit dieser E-Mail existiert, wurde eine E-Mail gesendet.');

        if ($user !== null) {
            $token = $this->auth->generatePasswordResetToken($user);

            $this->mail->send(
                to: $email,
                subject: 'Passwort zurücksetzen — ' . ($_ENV['APP_NAME'] ?? 'HuberCMS'),
                template: 'emails.password-reset',
                data: [
                    'user'  => $user,
                    'link'  => rtrim($_ENV['APP_URL'] ?? '', '/') . '/password/reset/' . $token,
                    'ttl'   => 60,
                ]
            );
        }

        return $this->redirect('/login');
    }

    public function showReset(Request $request, string $token): Response
    {
        $user = $this->auth->findUserByResetToken($token);

        if ($user === null) {
            $this->flashError('Ungültiger oder abgelaufener Link.');
            return $this->redirect('/password/forgot');
        }

        return $this->view('auth.password-reset', [
            'title' => 'Neues Passwort',
            'token' => htmlspecialchars($token, ENT_QUOTES),
        ]);
    }

    public function resetPassword(Request $request): Response
    {
        try {
            $this->validate($request, [
                'token'                 => 'required',
                'password'              => 'required|minlength:8',
                'password_confirmation' => 'required|confirmed',
            ]);
        } catch (ValidationException) {
            return $this->redirect('/password/forgot');
        }

        $user = $this->auth->findUserByResetToken((string) $request->post('token'));

        if ($user === null) {
            $this->flashError('Ungültiger oder abgelaufener Link.');
            return $this->redirect('/password/forgot');
        }

        $this->auth->resetPassword($user, (string) $request->post('password'));

        $this->flashSuccess('Passwort erfolgreich geändert. Bitte melde dich an.');
        return $this->redirect('/login');
    }
}
