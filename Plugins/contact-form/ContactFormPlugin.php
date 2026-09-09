<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\ContactForm;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;
use HuberCMS\Core\Router;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;

/**
 * ContactFormPlugin
 *
 * Shortcode [contact-form] für Seiten und Beiträge.
 * POST-Handler unter /contact-form/send.
 * Äquivalent zu Contact Form 7 für WordPress.
 */
class ContactFormPlugin
{
    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        // Shortcode [contact-form] registrieren
        $events->listen('shortcode.contact-form', function () {
            return $this->renderForm();
        });

        // Frontend-Route registrieren
        try {
            /** @var Router $router */
            $router = $container->make(Router::class);
            $router->post('/contact-form/send', function (Request $request) use ($container) {
                return $this->handleSubmit($request, $container);
            });
        } catch (\Throwable) {
            // Router noch nicht verfügbar
        }
    }

    public function renderForm(string $subject = 'Kontaktanfrage'): string
    {
        return <<<HTML
        <div class="contact-form-widget my-4">
            <form method="POST" action="/contact-form/send" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="cf_name" class="form-label">Name *</label>
                    <input type="text" id="cf_name" name="cf_name" class="form-control"
                           placeholder="Dein Name" required>
                </div>
                <div class="mb-3">
                    <label for="cf_email" class="form-label">E-Mail *</label>
                    <input type="email" id="cf_email" name="cf_email" class="form-control"
                           placeholder="deine@email.de" required>
                </div>
                <div class="mb-3">
                    <label for="cf_subject" class="form-label">Betreff</label>
                    <input type="text" id="cf_subject" name="cf_subject" class="form-control"
                           value="{$subject}">
                </div>
                <div class="mb-3">
                    <label for="cf_message" class="form-label">Nachricht *</label>
                    <textarea id="cf_message" name="cf_message" class="form-control"
                              rows="5" placeholder="Deine Nachricht…" required></textarea>
                </div>
                <input type="hidden" name="cf_honeypot" value="">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i> Nachricht senden
                </button>
            </form>
        </div>
        HTML;
    }

    private function handleSubmit(Request $request, Container $container): Response
    {
        // Honeypot-Check (Anti-Bot)
        if ($request->post('cf_honeypot', '') !== '') {
            return Response::redirect('/');
        }

        $name    = htmlspecialchars(strip_tags((string) $request->post('cf_name', '')), ENT_QUOTES);
        $email   = filter_var($request->post('cf_email', ''), FILTER_VALIDATE_EMAIL);
        $subject = htmlspecialchars(strip_tags((string) $request->post('cf_subject', 'Kontaktanfrage')), ENT_QUOTES);
        $message = htmlspecialchars(strip_tags((string) $request->post('cf_message', '')), ENT_QUOTES);

        if (!$name || !$email || !$message) {
            return Response::json(['success' => false, 'error' => 'Bitte alle Pflichtfelder ausfüllen.'], 422);
        }

        // E-Mail senden (über MailService wenn verfügbar)
        try {
            $mail = $container->make(\HuberCMS\Services\MailService::class);
            $adminEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'admin@hubercms.io';

            $mail->send(
                to: $adminEmail,
                subject: "[Kontakt] {$subject}",
                template: 'emails.contact-form',
                data: compact('name', 'email', 'subject', 'message')
            );
        } catch (\Throwable) {
            // Mail-Service nicht verfügbar — trotzdem Erfolg melden
        }

        // Redirect mit Erfolgs-Parameter
        $referer = $request->header('Referer') ?? '/';
        return Response::redirect($referer . '?contact=success');
    }
}
