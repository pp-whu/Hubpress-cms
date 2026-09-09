<?php

declare(strict_types=1);

namespace HuberCMS\Enums;

/**
 * PostStatus
 *
 * All possible states of a blog post or page.
 *
 * @package HuberCMS\Enums
 */
enum PostStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Trashed   = 'trashed';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Entwurf',
            self::Published => 'Veröffentlicht',
            self::Scheduled => 'Geplant',
            self::Trashed   => 'Papierkorb',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft     => 'badge-secondary',
            self::Published => 'badge-success',
            self::Scheduled => 'badge-info',
            self::Trashed   => 'badge-danger',
        };
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
