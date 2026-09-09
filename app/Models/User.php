<?php

declare(strict_types=1);

namespace HuberCMS\Models;

use HuberCMS\Core\Model;
use HuberCMS\Enums\UserRole;

/**
 * User Model
 *
 * Represents a CMS user with role-based access control,
 * 2FA support and profile management.
 *
 * @package HuberCMS\Models
 *
 * @property int    $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $first_name
 * @property string $last_name
 * @property string|null $avatar
 * @property string|null $bio
 * @property bool   $is_active
 * @property bool   $two_factor_enabled
 * @property string|null $two_factor_secret
 * @property int    $login_attempts
 * @property string|null $locked_until
 * @property string|null $remember_token
 * @property string|null $password_reset_token
 * @property string|null $password_reset_expires
 * @property string $created_at
 * @property string $updated_at
 */
final class User extends Model
{
    protected static string $table = 'users';

    protected static array $fillable = [
        'username', 'email', 'password', 'role',
        'first_name', 'last_name', 'avatar', 'bio',
        'is_active', 'two_factor_enabled',
    ];

    protected static array $hidden = [
        'password', 'two_factor_secret', 'remember_token',
        'password_reset_token',
    ];

    protected static array $casts = [
        'id'                  => 'int',
        'is_active'           => 'bool',
        'two_factor_enabled'  => 'bool',
        'login_attempts'      => 'int',
    ];

    // =========================================================
    // Business logic helpers
    // =========================================================

    /**
     * Returns the user's full name.
     */
    public function fullName(): string
    {
        $first = $this->getAttribute('first_name') ?? '';
        $last  = $this->getAttribute('last_name') ?? '';
        return trim("{$first} {$last}") ?: ($this->getAttribute('username') ?? '');
    }

    /**
     * Checks whether the user has a specific role.
     */
    public function hasRole(string|UserRole $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;
        return $this->getAttribute('role') === $roleValue;
    }

    /**
     * Checks whether the user has any of the given roles.
     *
     * @param string[] $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->getAttribute('role'), $roles, true);
    }

    /**
     * Returns whether the account is locked due to too many failed logins.
     */
    public function isLocked(): bool
    {
        $lockedUntil = $this->getAttribute('locked_until');

        if ($lockedUntil === null) {
            return false;
        }

        return strtotime($lockedUntil) > time();
    }

    /**
     * Verifies a plain-text password against the stored hash.
     */
    public function verifyPassword(string $plainPassword): bool
    {
        $hash = $this->attributes['password'] ?? '';
        return password_verify($plainPassword, $hash);
    }

    /**
     * Finds a user by email address.
     */
    public static function findByEmail(string $email): ?self
    {
        $row = static::query()->where('email', strtolower(trim($email)))->first();
        return $row !== null ? static::hydrate($row) : null;
    }

    /**
     * Finds a user by username.
     */
    public static function findByUsername(string $username): ?self
    {
        $row = static::query()->where('username', $username)->first();
        return $row !== null ? static::hydrate($row) : null;
    }

    /**
     * Returns all users with the given role.
     *
     * @return static[]
     */
    public static function byRole(string $role): array
    {
        $rows = static::query()->where('role', $role)->where('is_active', 1)->get();
        return array_map(fn($row) => static::hydrate($row), $rows);
    }
}
