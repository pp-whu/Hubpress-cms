<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\SocialShare;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;

/**
 * SocialSharePlugin
 *
 * Teilen-Buttons für Beiträge und Seiten.
 * Shortcode: [social-share]
 */
class SocialSharePlugin
{
    /** Konfigurierbare Netzwerke */
    private array $networks = [
        'facebook'  => ['label' => 'Facebook',  'icon' => 'bi-facebook',  'color' => '#1877f2', 'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}'],
        'twitter'   => ['label' => 'X / Twitter','icon' => 'bi-twitter-x', 'color' => '#000000', 'url' => 'https://twitter.com/intent/tweet?url={url}&text={title}'],
        'linkedin'  => ['label' => 'LinkedIn',   'icon' => 'bi-linkedin',  'color' => '#0a66c2', 'url' => 'https://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}'],
        'whatsapp'  => ['label' => 'WhatsApp',   'icon' => 'bi-whatsapp',  'color' => '#25d366', 'url' => 'https://wa.me/?text={title}%20{url}'],
        'telegram'  => ['label' => 'Telegram',   'icon' => 'bi-telegram',  'color' => '#2ca5e0', 'url' => 'https://t.me/share/url?url={url}&text={title}'],
        'email'     => ['label' => 'E-Mail',     'icon' => 'bi-envelope',  'color' => '#64748b', 'url' => 'mailto:?subject={title}&body={url}'],
    ];

    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        // Shortcode registrieren
        $events->listen('shortcode.social-share', function () {
            return $this->renderButtons();
        });

        // Auto-Anhang nach Beitrags-Inhalt
        $events->listen('post.content.after', function (string $content, array $post) {
            return $content . $this->renderButtons(
                url: rtrim($_ENV['APP_URL'] ?? '', '/') . '/blog/' . ($post['slug'] ?? ''),
                title: $post['title'] ?? ''
            );
        });
    }

    /**
     * Rendert die Share-Buttons.
     */
    public function renderButtons(string $url = '', string $title = ''): string
    {
        if (empty($url)) {
            $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
        }

        $encodedUrl   = urlencode($url);
        $encodedTitle = urlencode($title);

        $html  = '<div class="social-share-buttons d-flex align-items-center gap-2 flex-wrap my-4">';
        $html .= '<span class="text-muted small me-1">Teilen:</span>';

        foreach ($this->networks as $key => $net) {
            $shareUrl = str_replace(['{url}', '{title}'], [$encodedUrl, $encodedTitle], $net['url']);
            $html .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" '
                . 'class="btn btn-sm" '
                . 'style="background:%s;color:#fff;border:none" '
                . 'title="%s teilen">'
                . '<i class="bi %s me-1"></i>%s'
                . '</a>',
                htmlspecialchars($shareUrl, ENT_QUOTES),
                $net['color'],
                htmlspecialchars($net['label'], ENT_QUOTES),
                $net['icon'],
                htmlspecialchars($net['label'], ENT_QUOTES)
            );
        }

        $html .= '</div>';
        return $html;
    }
}
