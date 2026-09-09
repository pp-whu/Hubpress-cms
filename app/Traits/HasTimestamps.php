<?php

declare(strict_types=1);

namespace HuberCMS\Traits;

/**
 * HasTimestamps
 *
 * Provides automatic created_at / updated_at handling for models.
 * Mixed into Model classes that use $timestamps = true.
 *
 * @package HuberCMS\Traits
 */
trait HasTimestamps
{
    /**
     * Returns the created_at timestamp as a DateTimeImmutable.
     */
    public function createdAt(): ?\DateTimeImmutable
    {
        $value = $this->attributes['created_at'] ?? null;
        return $value ? new \DateTimeImmutable((string) $value) : null;
    }

    /**
     * Returns the updated_at timestamp as a DateTimeImmutable.
     */
    public function updatedAt(): ?\DateTimeImmutable
    {
        $value = $this->attributes['updated_at'] ?? null;
        return $value ? new \DateTimeImmutable((string) $value) : null;
    }

    /**
     * Returns a human-readable "time ago" string.
     */
    public function timeAgo(string $field = 'created_at'): string
    {
        $timestamp = $this->attributes[$field] ?? null;
        if ($timestamp === null) return '–';

        $diff = time() - strtotime((string) $timestamp);

        return match (true) {
            $diff < 60      => 'Gerade eben',
            $diff < 3600    => round($diff / 60) . ' Min. ago',
            $diff < 86400   => round($diff / 3600) . ' Std. ago',
            $diff < 604800  => round($diff / 86400) . ' Tag(e) ago',
            default         => date('d.m.Y', strtotime((string) $timestamp)),
        };
    }
}
