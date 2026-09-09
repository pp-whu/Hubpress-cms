<?php

declare(strict_types=1);

namespace HuberCMS\Services;

use HuberCMS\Core\Session;
use HuberCMS\Core\Logger;
use HuberCMS\Core\EventDispatcher;
use HuberCMS\Models\User;

/**
 * AuthService
 *
 * Handles all authentication logic:
 * - Login / Logout
 * - Brute-force protection (max 5 attempts → 15 min lock)
 * - Password hashing and verification
 * - Password reset token generation
 * - 2FA validation placeholder
 *
 * @package HuberCMS\Services
 */
final class AuthService
{
    private const MAX_ATTEMPTS   = 5;
    private const LOCK_DURATION  = 900; // 15 minutes

    public function __construct(
        private readonly Session         $session,
        private readonly Logger          $logger,
        private readonly EventDispatcher $events
    ) {
    }

    // =========================================================
    // Login
    // =========================================================

    /**
     * Attempts to authenticate a user by email and password.
     *
     * @return array{success:bool, error?:string, user?:User, requires_2fa?:bool}
     */
    public function attempt(string $email, string $password, bool $remember = false): array
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            $this->logger->channel('auth')->warning('Login attempt with unknown email', ['email' => $email]);
            // Same response as wrong password (prevents email enumeration)
            return ['success' => false, 'error' => 'Ungültige Anmeldedaten.'];
        }

        if ($user->isLocked()) {
            return ['success' => false, 'error' => 'Konto temporär gesperrt. Bitte versuche es später.'];
        }

        if (!$user->verifyPassword($password)) {
            $this->incrementFailedAttempts($user);
            return ['success' => false, 'error' => 'Ungültige Anmeldedaten.'];
        }

        if (!(bool) $user->getAttribute('is_active')) {
            return ['success' => false, 'error' => 'Dein Konto ist deaktiviert.'];
        }

        // 2FA check
        if ((bool) $user->getAttribute('two_factor_enabled')) {
            // Store pending user ID — 2FA step required
            $this->session->set('2fa_pending_user_id', $user->getAttribute('id'));
            return ['success' => true, 'requires_2fa' => true, 'user' => $user];
        }

        $this->loginUser($user, $remember);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Logs in a user directly (after 2FA or OAuth).
     */
    public function loginUser(User $user, bool $remember = false): void
    {
        $this->session->regenerate();
        $this->session->regenerateCsrf();
        $this->resetFailedAttempts($user);

        $userData = [
            'id'       => $user->getAttribute('id'),
            'username' => $user->getAttribute('username'),
            'email'    => $user->getAttribute('email'),
            'role'     => $user->getAttribute('role'),
            'avatar'   => $user->getAttribute('avatar'),
        ];

        $this->session->set('auth_user', $userData);
        $this->session->set('auth_time', time());

        $this->logger->channel('auth')->info('User logged in', [
            'user_id' => $user->getAttribute('id'),
            'email'   => $user->getAttribute('email'),
        ]);

        $this->events->dispatch('auth.login', $user);
    }

    // =========================================================
    // Logout
    // =========================================================

    public function logout(): void
    {
        $user = $this->session->get('auth_user');

        $this->logger->channel('auth')->info('User logged out', [
            'user_id' => $user['id'] ?? null,
        ]);

        $this->events->dispatch('auth.logout', $user);
        $this->session->destroy();
    }

    // =========================================================
    // Current user
    // =========================================================

    /**
     * Returns the currently authenticated user array or null.
     *
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return $this->session->get('auth_user');
    }

    public function check(): bool
    {
        return $this->session->get('auth_user') !== null;
    }

    public function id(): ?int
    {
        $user = $this->session->get('auth_user');
        return isset($user['id']) ? (int) $user['id'] : null;
    }

    // =========================================================
    // Password management
    // =========================================================

    /**
     * Hashes a plain-text password using bcrypt.
     */
    public function hashPassword(string $password): string
    {
        $rounds = (int) ($_ENV['BCRYPT_ROUNDS'] ?? 12);
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $rounds]);
    }

    /**
     * Generates a secure password reset token for the user.
     * Returns the plain token — the hashed version is stored in DB.
     */
    public function generatePasswordResetToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $user->setAttribute('password_reset_token', $hashedToken);
        $user->setAttribute('password_reset_expires', $expires);
        $user->save();

        return $token;
    }

    /**
     * Validates a password reset token and returns the matching user or null.
     */
    public function findUserByResetToken(string $token): ?User
    {
        $hashedToken = hash('sha256', $token);

        $row = User::query()
            ->where('password_reset_token', $hashedToken)
            ->whereRaw('password_reset_expires > NOW()')
            ->first();

        if ($row === null) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return User::find($row['id']);
    }

    /**
     * Resets the password for a user and invalidates the reset token.
     */
    public function resetPassword(User $user, string $newPassword): void
    {
        $user->setAttribute('password', $this->hashPassword($newPassword));
        $user->setAttribute('password_reset_token', null);
        $user->setAttribute('password_reset_expires', null);
        $user->save();

        $this->logger->channel('auth')->info('Password reset', ['user_id' => $user->getAttribute('id')]);
    }

    // =========================================================
    // Brute-force protection
    // =========================================================

    private function incrementFailedAttempts(User $user): void
    {
        $attempts = (int) $user->getAttribute('login_attempts') + 1;
        $user->setAttribute('login_attempts', $attempts);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + self::LOCK_DURATION);
            $user->setAttribute('locked_until', $lockedUntil);

            $this->logger->channel('auth')->warning('Account locked due to failed attempts', [
                'user_id'      => $user->getAttribute('id'),
                'locked_until' => $lockedUntil,
            ]);
        }

        $user->save();
    }

    private function resetFailedAttempts(User $user): void
    {
        $user->setAttribute('login_attempts', 0);
        $user->setAttribute('locked_until', null);
        $user->save();
    }
}
