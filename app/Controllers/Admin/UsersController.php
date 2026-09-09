<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Database;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Services\AuthService;
use HuberCMS\Models\User;
use HuberCMS\Enums\UserRole;
use HuberCMS\Controllers\ValidationException;

/**
 * UsersController — Admin user management
 *
 * @package HuberCMS\Controllers\Admin
 */
final class UsersController extends Controller
{
    public function __construct(
        Session                      $session,
        private readonly Database    $db,
        private readonly AuthService $auth
    ) {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $p = $this->db->prefix();
        $users = $this->db->select("SELECT * FROM `{$p}users` ORDER BY created_at DESC");

        return $this->view('admin.users.index', [
            'title' => 'Benutzer',
            'users' => $users,
            'roles' => UserRole::cases(),
            'currentRoute' => 'admin.users',
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->view('admin.users.form', [
            'title'  => 'Benutzer erstellen',
            'user'   => null,
            'roles'  => UserRole::cases(),
            'currentRoute' => 'admin.users',
        ]);
    }

    public function store(Request $request): Response
    {
        try {
            $this->validate($request, [
                'username' => 'required|alphanumeric|minlength:3|maxlength:50',
                'email'    => 'required|email|maxlength:255',
                'password' => 'required|minlength:8',
                'role'     => 'required|in:' . implode(',', UserRole::values()),
            ]);
        } catch (ValidationException) {
            return $this->redirect('/admin/benutzer/neu');
        }

        User::create([
            'username'  => (string) $request->post('username'),
            'email'     => strtolower(trim((string) $request->post('email'))),
            'password'  => $this->auth->hashPassword((string) $request->post('password')),
            'role'      => (string) $request->post('role'),
            'is_active' => (bool) $request->post('is_active', 1),
        ]);

        $this->flashSuccess('Benutzer erfolgreich erstellt.');
        return $this->redirect('/admin/benutzer');
    }

    public function edit(Request $request, string $id): Response
    {
        $user = User::find((int) $id);

        if ($user === null) {
            $this->flashError('Benutzer nicht gefunden.');
            return $this->redirect('/admin/benutzer');
        }

        return $this->view('admin.users.form', [
            'title'  => 'Benutzer bearbeiten',
            'user'   => $user,
            'roles'  => UserRole::cases(),
            'currentRoute' => 'admin.users',
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $role = (string) $request->post('role', '');
        if (!in_array($role, UserRole::values(), true)) {
            $this->flashError('Ungültige Rolle.');
            return $this->redirect('/admin/benutzer/' . (int) $id);
        }

        $user = User::find((int) $id);

        if ($user === null) {
            $this->flashError('Benutzer nicht gefunden.');
            return $this->redirect('/admin/benutzer');
        }

        $user->setAttribute('role', $role);
        $user->setAttribute('is_active', (bool) $request->post('is_active', $user->is_active));
        $user->setAttribute('first_name', (string) $request->post('first_name', ''));
        $user->setAttribute('last_name', (string) $request->post('last_name', ''));

        $newPassword = (string) $request->post('password', '');
        if (!empty($newPassword) && strlen($newPassword) >= 8) {
            $user->setAttribute('password', $this->auth->hashPassword($newPassword));
        }

        $user->save();

        $this->flashSuccess('Benutzer aktualisiert.');
        return $this->redirect('/admin/benutzer');
    }

    public function delete(Request $request, string $id): Response
    {
        $authUserId = $this->session->get('auth_user')['id'] ?? null;

        if ((int) $id === (int) $authUserId) {
            $this->flashError('Du kannst dich nicht selbst löschen.');
            return $this->redirect('/admin/benutzer');
        }

        $user = User::find((int) $id);
        if ($user !== null) {
            $user->delete();
        }

        if ($request->wantsJson()) {
            return $this->json(['success' => true, 'message' => 'Benutzer gelöscht.']);
        }

        $this->flashSuccess('Benutzer gelöscht.');
        return $this->redirect('/admin/benutzer');
    }
}
