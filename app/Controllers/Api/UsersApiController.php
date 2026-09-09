<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Api;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};
use HuberCMS\Models\User;

/** UsersApiController @package HuberCMS\Controllers\Api */
final class UsersApiController extends Controller
{
    public function __construct(Session $session) { parent::__construct($session); }

    public function index(Request $request): Response {
        return $this->json(array_map(fn($u) => $u->toArray(), User::all()));
    }

    public function show(Request $request, string $id): Response {
        $user = User::find((int) $id);
        return $user !== null ? $this->json($user->toArray()) : $this->json(['error' => 'Not found'], 404);
    }
}
