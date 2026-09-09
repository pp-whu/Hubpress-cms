<?php

declare(strict_types=1);

namespace HuberCMS\Helpers;

/**
 * SlugHelper
 *
 * Generates URL-friendly slugs from arbitrary strings.
 * Handles German umlauts and special characters.
 *
 * @package HuberCMS\Helpers
 */
final class SlugHelper
{
    /** @var array<string, string> German umlaut replacements */
    private const UMLAUT_MAP = [
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue',
        'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue',
        'ß' => 'ss',
    ];

    /**
     * Converts a string to a URL-safe slug.
     *
     * @param string $separator Word separator (default: '-')
     */
    public static function make(string $text, string $separator = '-', int $maxLength = 200): string
    {
        // Replace German umlauts
        $text = strtr($text, self::UMLAUT_MAP);

        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // Replace non-alphanumeric with separator
        $text = preg_replace('/[^a-z0-9]+/', $separator, $text) ?? $text;

        // Trim separators and limit length
        $text = trim($text, $separator);
        $text = mb_substr($text, 0, $maxLength);

        return $text ?: 'slug';
    }

    /**
     * Ensures a slug is unique by appending a counter suffix.
     *
     * @param callable(string): bool $existsCallback Returns true if slug exists
     */
    public static function makeUnique(string $text, callable $existsCallback, string $separator = '-'): string
    {
        $base = self::make($text, $separator);
        $slug = $base;
        $counter = 1;

        while ($existsCallback($slug)) {
            $slug = $base . $separator . $counter;
            $counter++;
        }

        return $slug;
    }
}
