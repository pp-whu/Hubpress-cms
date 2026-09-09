<?php

declare(strict_types=1);

namespace HuberCMS\Enums;

/**
 * UserRole
 *
 * Defines all available user roles in HuberCMS.
 *
 * @package HuberCMS\Enums
 */
enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Editor     = 'editor';
    case Author     = 'author';
    case Contributor = 'contributor';
    case Subscriber = 'subscriber';

    /**
     * Returns the human-readable German label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin   => 'Super-Administrator',
            self::Admin        => 'Administrator',
            self::Editor       => 'Redakteur',
            self::Author       => 'Autor',
            self::Contributor  => 'Mitarbeiter',
            self::Subscriber   => 'Abonnent',
        };
    }

    /**
     * Returns the permission level (higher = more access).
     */
    public function level(): int
    {
        return match ($this) {
            self::SuperAdmin   => 100,
            self::Admin        => 80,
            self::Editor       => 60,
            self::Author       => 40,
            self::Contributor  => 20,
            self::Subscriber   => 10,
        };
    }

    /**
     * Checks whether this role has at least the given level.
     */
    public function isAtLeast(self $role): bool
    {
        return $this->level() >= $role->level();
    }

    /** @return string[] All role values */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
