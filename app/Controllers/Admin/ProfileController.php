<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};
use HuberCMS\Services\AuthService;
use HuberCMS\Models\User;

/** ProfileController — Admin user's own profile @package HuberCMS\Controllers\Admin */
final class ProfileController extends Controller
{
    public function __construct(Session $session, private readonly AuthService $auth) { parent::__construct($session); }

    public function index(Request $request): Response {
        $userId = (int) ($this->session->get('auth_user')['id'] ?? 0);
        $user = User::find($userId);
        return $this->view('admin.profile.index', ['title' => 'Mein Profil', 'user' => $user, 'currentRoute' => 'admin.profile']);
    }

    public function update(Request $request): Response {
        $userId = (int) ($this->session->get('auth_user')['id'] ?? 0);
        $user = User::find($userId);
        if ($user === null) return $this->redirect('/admin');

        $user->setAttribute('first_name', strip_tags((string) $request->post('first_name', '')));
        $user->setAttribute('last_name', strip_tags((string) $request->post('last_name', '')));
        $user->setAttribute('bio', strip_tags((string) $request->post('bio', '')));

        $newPassword = (string) $request->post('password', '');
        if (!empty($newPassword) && strlen($newPassword) >= 8) {
            $user->setAttribute('password', $this->auth->hashPassword($newPassword));
        }

        $user->save();
        $this->flashSuccess('Profil aktualisiert.');
        return $this->redirect('/admin/profil');
    }
}
