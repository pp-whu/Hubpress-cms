<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Frontend;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Models\User;
use HuberCMS\Services\AuthService;

/**
 * AccountController — Protected user profile area
 * @package HuberCMS\Controllers\Frontend
 */
final class AccountController extends Controller
{
    public function __construct(Session $session, private readonly AuthService $auth)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        return $this->view('frontend.account.index', [
            'title' => 'Mein Konto',
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $this->flashSuccess('Profil aktualisiert.');
        return $this->redirect('/mein-konto');
    }

    public function changePassword(Request $request): Response
    {
        $userId  = (int) ($this->session->get('auth_user')['id'] ?? 0);
        $user    = User::find($userId);

        if ($user === null) {
            return $this->redirect('/login');
        }

        $current  = (string) $request->post('current_password', '');
        $new      = (string) $request->post('password', '');
        $confirm  = (string) $request->post('password_confirmation', '');

        if (!$user->verifyPassword($current)) {
            $this->flashError('Das aktuelle Passwort ist falsch.');
            return $this->redirect('/mein-konto');
        }

        if (strlen($new) < 8) {
            $this->flashError('Das neue Passwort muss mindestens 8 Zeichen haben.');
            return $this->redirect('/mein-konto');
        }

        if ($new !== $confirm) {
            $this->flashError('Die Passwörter stimmen nicht überein.');
            return $this->redirect('/mein-konto');
        }

        $user->setAttribute('password', $this->auth->hashPassword($new));
        $user->save();

        $this->flashSuccess('Passwort erfolgreich geändert.');
        return $this->redirect('/mein-konto');
    }
}
