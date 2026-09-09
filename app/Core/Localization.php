<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * Localization
 *
 * Simple translation/localization system.
 * Translation files are PHP arrays in app/Locales/{locale}/messages.php.
 *
 * Usage:
 *   $t = new Localization('de');
 *   echo $t->get('auth.login_failed');    // → 'Anmeldung fehlgeschlagen'
 *   echo $t->get('user.greeting', ['name' => 'Max']); // → 'Hallo, Max!'
 *
 * @package HuberCMS\Core
 */
final class Localization
{
    /** @var array<string, array<string, string>> Loaded locale data */
    private array $translations = [];

    public function __construct(
        private string $locale,
        private readonly string $fallbackLocale = 'en',
        private readonly string $localesPath = APP_PATH . '/Locales'
    ) {
    }

    /**
     * Returns a translated string by dot-notation key.
     *
     * @param array<string, string> $replacements  e.g. ['name' => 'Max']
     */
    public function get(string $key, array $replacements = []): string
    {
        $translation = $this->resolve($key, $this->locale)
            ?? $this->resolve($key, $this->fallbackLocale)
            ?? $key;

        if (!empty($replacements)) {
            foreach ($replacements as $placeholder => $value) {
                $translation = str_replace(':' . $placeholder, $value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Alias for get().
     *
     * @param array<string, string> $replacements
     */
    public function trans(string $key, array $replacements = []): string
    {
        return $this->get($key, $replacements);
    }

    /**
     * Sets the active locale.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    // =========================================================
    // Private helpers
    // =========================================================

    private function resolve(string $key, string $locale): ?string
    {
        $this->loadLocale($locale);

        $parts = explode('.', $key, 2);
        $group = $parts[0];
        $subkey = $parts[1] ?? null;

        if (!isset($this->translations[$locale][$group])) {
            return null;
        }

        if ($subkey === null) {
            $value = $this->translations[$locale][$group];
            return is_string($value) ? $value : null;
        }

        $data = $this->translations[$locale][$group];
        foreach (explode('.', $subkey) as $segment) {
            if (!is_array($data) || !isset($data[$segment])) {
                return null;
            }
            $data = $data[$segment];
        }

        return is_string($data) ? $data : null;
    }

    private function loadLocale(string $locale): void
    {
        if (isset($this->translations[$locale])) {
            return;
        }

        $this->translations[$locale] = [];
        $localeDir = $this->localesPath . '/' . $locale;

        if (!is_dir($localeDir)) {
            return;
        }

        foreach (glob($localeDir . '/*.php') ?: [] as $file) {
            $group = pathinfo($file, PATHINFO_FILENAME);
            $data = require $file;

            if (is_array($data)) {
                $this->translations[$locale][$group] = $data;
            }
        }
    }
}
