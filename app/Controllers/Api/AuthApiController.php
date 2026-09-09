<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Api;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};
use HuberCMS\Services\{AuthService, JwtService};

/**
 * AuthApiController — REST Auth endpoints
 * @package HuberCMS\Controllers\Api
 */
final class AuthApiController extends Controller
{
    public function __construct(Session $session, private readonly AuthService $auth, private readonly JwtService $jwt) { parent::__construct($session); }

    public function login(Request $request): Response {
        $email    = (string) $request->json('email', '');
        $password = (string) $request->json('password', '');

        if (empty($email) || empty($password)) return $this->json(['error' => 'email and password required'], 400);

        $result = $this->auth->attempt($email, $password);

        if (!$result['success']) return $this->json(['error' => $result['error'] ?? 'Unauthorized'], 401);

        $user  = $result['user'];
        $token = $this->jwt->encode($user->toArray());

        return $this->json(['token' => $token, 'token_type' => 'Bearer', 'expires_in' => 3600, 'user' => $user->toArray()]);
    }

    public function refresh(Request $request): Response {
        $token = substr($request->header('Authorization') ?? '', 7);
        try { $newToken = $this->jwt->refresh($token); return $this->json(['token' => $newToken]); }
        catch (\Throwable) { return $this->json(['error' => 'Invalid or expired token.'], 401); }
    }

    public function logout(Request $request): Response { return $this->json(['message' => 'Logged out.']); }

    public function me(Request $request): Response {
        $token = substr($request->header('Authorization') ?? '', 7);
        try { $payload = $this->jwt->decode($token); return $this->json((array) $payload->user); }
        catch (\Throwable $e) { return $this->json(['error' => 'Invalid token'], 401); }
    }
}
