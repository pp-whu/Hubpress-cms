<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\AntiSpam;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;

/**
 * AntiSpamPlugin
 *
 * Filtert Kommentare auf Spam-Muster.
 * Äquivalent zu Akismet für WordPress.
 */
class AntiSpamPlugin
{
    /** Typische Spam-Keywords */
    private const SPAM_KEYWORDS = [
        'casino', 'poker', 'viagra', 'cialis', 'cheap', 'buy now',
        'click here', 'earn money', 'free money', 'lose weight',
        'bitcoin investment', 'crypto profit', 'make $', 'work from home',
    ];

    /** Max erlaubte Links in einem Kommentar */
    private const MAX_LINKS = 3;

    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        // Kommentar vor dem Speichern prüfen
        $events->listen('comment.before_save', function (array $comment) {
            $result = $this->checkSpam($comment);

            if ($result['is_spam']) {
                $comment['status'] = 'spam';
                $comment['_spam_reason'] = $result['reason'];
            }

            return $comment;
        });
    }

    /**
     * Prüft ob ein Kommentar Spam ist.
     *
     * @param array{content:string, author_email?:string, author_name?:string} $comment
     * @return array{is_spam:bool, reason:string}
     */
    public function checkSpam(array $comment): array
    {
        $content = strtolower($comment['content'] ?? '');

        // Keyword-Check
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (str_contains($content, $keyword)) {
                return ['is_spam' => true, 'reason' => "Spam-Keyword gefunden: {$keyword}"];
            }
        }

        // Link-Count-Check
        $linkCount = substr_count($content, 'http://') + substr_count($content, 'https://');
        if ($linkCount > self::MAX_LINKS) {
            return ['is_spam' => true, 'reason' => "Zu viele Links ({$linkCount})"];
        }

        // Sehr kurze Kommentare mit Links
        if (strlen($content) < 20 && $linkCount > 0) {
            return ['is_spam' => true, 'reason' => 'Kurzer Kommentar mit Link'];
        }

        // Alle Großbuchstaben (Screaming)
        $alpha = preg_replace('/[^a-zA-Z]/', '', $content);
        if (strlen($alpha) > 10 && strtoupper($alpha) === $alpha) {
            return ['is_spam' => true, 'reason' => 'Nur Großbuchstaben'];
        }

        return ['is_spam' => false, 'reason' => ''];
    }
}
