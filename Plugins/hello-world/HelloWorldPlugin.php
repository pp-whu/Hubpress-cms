<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\HelloWorld;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;

/**
 * HelloWorldPlugin
 *
 * Zeigt einen zufälligen Spruch im Admin-Dashboard.
 * Das klassische "Hello Dolly" für HuberCMS.
 */
class HelloWorldPlugin
{
    private const QUOTES = [
        'Der beste Zeitpunkt einen Baum zu pflanzen war vor 20 Jahren. Der zweitbeste ist jetzt.',
        'Jede Reise beginnt mit einem einzigen Schritt.',
        'Der Code, den du heute schreibst, ist das Fundament von morgen.',
        'Einfachheit ist die höchste Form der Raffinesse.',
        'Erst denken, dann coden.',
        'Make it work, make it right, make it fast.',
        'Das Beste an einem Fehler ist, dass man etwas lernt.',
        'Good code is its own best documentation.',
        'Jedes große Projekt begann als kleines.',
        'Perfektion ist der Feind des Guten.',
        'Halte es einfach, halte es klar.',
        'Wer aufhört besser zu werden, hat aufgehört gut zu sein.',
    ];

    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        // Spruch ins Dashboard einfügen
        $events->listen('admin.dashboard.widgets', function (array $widgets) {
            $quote = self::QUOTES[array_rand(self::QUOTES)];
            $widgets[] = [
                'id'      => 'hello-world',
                'title'   => 'Hello World',
                'content' => '<blockquote class="blockquote mb-0"><p class="small fst-italic text-muted">&bdquo;' . htmlspecialchars($quote, ENT_QUOTES) . '&ldquo;</p></blockquote>',
            ];
            return $widgets;
        });
    }

    public static function randomQuote(): string
    {
        return self::QUOTES[array_rand(self::QUOTES)];
    }
}
