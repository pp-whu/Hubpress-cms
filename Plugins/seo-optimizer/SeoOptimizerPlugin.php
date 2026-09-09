<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\SeoOptimizer;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;

/**
 * SeoOptimizerPlugin
 *
 * Erweiterte SEO-Funktionen für HuberCMS.
 * Äquivalent zu Yoast SEO für WordPress.
 */
class SeoOptimizerPlugin
{
    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        // Meta-Tags für Frontend-Seiten
        $events->listen('frontend.head.meta', function (array $meta, array $page) {
            return $this->buildMeta($meta, $page);
        });

        // SEO-Score für Beitragseditor
        $events->listen('post.seo_score', function (array $post) {
            return $this->analyzeSeo($post);
        });

        // Schema.org JSON-LD
        $events->listen('frontend.head.schema', function (array $schemas, array $page) {
            $schemas[] = $this->buildSchema($page);
            return $schemas;
        });
    }

    /**
     * Baut vollständige Meta-Tags.
     *
     * @param array<string,string> $meta
     * @param array<string,mixed>  $page
     * @return array<string,string>
     */
    public function buildMeta(array $meta, array $page): array
    {
        $appUrl   = rtrim($_ENV['APP_URL'] ?? '', '/');
        $title    = $page['meta_title']       ?? $page['title'] ?? '';
        $desc     = $page['meta_description'] ?? '';
        $image    = $page['og_image']         ?? '';
        $url      = $page['canonical']        ?? $appUrl . ($page['slug'] ? '/blog/' . $page['slug'] : '/');

        // Open Graph
        $meta['og:title']       = $title;
        $meta['og:description'] = $desc;
        $meta['og:url']         = $url;
        $meta['og:type']        = isset($page['published_at']) ? 'article' : 'website';
        $meta['og:site_name']   = $_ENV['APP_NAME'] ?? 'HuberCMS';

        if ($image) {
            $meta['og:image'] = str_starts_with($image, 'http') ? $image : $appUrl . $image;
        }

        // Twitter Card
        $meta['twitter:card']        = 'summary_large_image';
        $meta['twitter:title']       = $title;
        $meta['twitter:description'] = $desc;

        // Canonical
        $meta['canonical'] = $url;

        return $meta;
    }

    /**
     * SEO-Analyse für einen Beitrag (0–100 Score).
     *
     * @param array<string,mixed> $post
     * @return array{score:int, issues:string[], suggestions:string[]}
     */
    public function analyzeSeo(array $post): array
    {
        $issues      = [];
        $suggestions = [];
        $score       = 100;

        $title   = $post['title']            ?? '';
        $content = strip_tags($post['content'] ?? '');
        $meta    = $post['meta_description']  ?? '';
        $slug    = $post['slug']              ?? '';

        // Titel-Länge
        if (strlen($title) < 30) { $issues[] = 'Titel zu kurz (min. 30 Zeichen)'; $score -= 15; }
        if (strlen($title) > 60) { $suggestions[] = 'Titel zu lang (max. 60 Zeichen empfohlen)'; $score -= 5; }

        // Meta-Beschreibung
        if (empty($meta)) { $issues[] = 'Meta-Beschreibung fehlt'; $score -= 20; }
        elseif (strlen($meta) < 80) { $suggestions[] = 'Meta-Beschreibung zu kurz'; $score -= 5; }
        elseif (strlen($meta) > 160) { $suggestions[] = 'Meta-Beschreibung zu lang (max. 160)'; $score -= 5; }

        // Inhaltslänge
        $wordCount = str_word_count($content);
        if ($wordCount < 300) { $issues[] = "Inhalt zu kurz ({$wordCount} Wörter, empfohlen: 300+)"; $score -= 15; }

        // Fokus-Keyword im Slug
        if (empty($slug)) { $suggestions[] = 'Kein URL-Slug gesetzt'; $score -= 10; }

        // Überschriften
        if (!str_contains($post['content'] ?? '', '## ') && !str_contains($post['content'] ?? '', '<h2')) {
            $suggestions[] = 'Keine H2-Überschriften gefunden'; $score -= 5;
        }

        return [
            'score'       => max(0, $score),
            'issues'      => $issues,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Baut Schema.org JSON-LD.
     *
     * @param array<string,mixed> $page
     * @return array<string,mixed>
     */
    public function buildSchema(array $page): array
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

        if (isset($page['published_at'])) {
            return [
                '@context'         => 'https://schema.org',
                '@type'            => 'BlogPosting',
                'headline'         => $page['title'] ?? '',
                'description'      => $page['meta_description'] ?? '',
                'datePublished'    => $page['published_at'] ?? '',
                'dateModified'     => $page['updated_at'] ?? $page['published_at'] ?? '',
                'url'              => $appUrl . '/blog/' . ($page['slug'] ?? ''),
                'publisher'        => [
                    '@type' => 'Organization',
                    'name'  => $_ENV['APP_NAME'] ?? 'HuberCMS',
                    'url'   => $appUrl,
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type'    => 'WebPage',
            'name'     => $page['title'] ?? '',
            'url'      => $appUrl . '/seite/' . ($page['slug'] ?? ''),
        ];
    }
}
