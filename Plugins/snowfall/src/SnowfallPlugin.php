<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\Snowfall;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;

/**
 * SnowfallPlugin
 *
 * Injiziert Schneeflocken-Animation (CSS + JS) ins Frontend.
 * Flocken fallen pendelnd von oben nach unten am linken und
 * rechten Bildschirmrand. Im Light-Modus sind sie bunt.
 *
 * Struktur:
 *   src/        PHP-Klassen
 *   assets/css  Stylesheet
 *   assets/js   Frontend-Script
 */
class SnowfallPlugin
{
    private const ASSETS_DIR = __DIR__ . '/../assets';

    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        $events->listen('frontend.body.end', fn(): string => $this->markup());
    }

    /**
     * Baut das injizierte Markup aus den Asset-Dateien zusammen.
     * Inline nötig, da /Plugins/ außerhalb des Docroots liegt.
     */
    private function markup(): string
    {
        $css = $this->readAsset('css/snowfall.css');
        $js  = $this->readAsset('js/snowfall.js');

        if ($css === '' && $js === '') {
            return '';
        }

        return "<style>\n{$css}</style>\n<script>\n{$js}</script>";
    }

    private function readAsset(string $relativePath): string
    {
        $path = self::ASSETS_DIR . '/' . $relativePath;

        return is_readable($path) ? (string) file_get_contents($path) : '';
    }
}
