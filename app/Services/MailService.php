<?php

declare(strict_types=1);

namespace HuberCMS\Services;

use HuberCMS\Core\Config;
use HuberCMS\Core\Logger;
use HuberCMS\Core\View;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * MailService
 *
 * Sends emails using PHPMailer with template rendering support.
 * Drivers: smtp | sendmail | log (development).
 *
 * @package HuberCMS\Services
 */
final class MailService
{
    public function __construct(
        private readonly Config $config,
        private readonly Logger $logger
    ) {
    }

    /**
     * Sends an email.
     *
     * @param string|array{email:string,name:string} $to
     * @param array<string, mixed>                   $data    Template variables
     * @throws \RuntimeException On send failure
     */
    public function send(
        string|array $to,
        string $subject,
        string $template,
        array $data = [],
        ?string $replyTo = null
    ): bool {
        $driver = $this->config->get('mail.driver', 'log');

        // Log driver — just dump to log (for development)
        if ($driver === 'log') {
            $this->logger->channel('app')->info('MAIL [log driver]', [
                'to'       => $to,
                'subject'  => $subject,
                'template' => $template,
            ]);
            return true;
        }

        $body = View::render($template, $data);

        $mail = $this->createMailer();

        try {
            // Recipient
            if (is_array($to)) {
                $mail->addAddress($to['email'], $to['name'] ?? '');
            } else {
                $mail->addAddress($to);
            }

            if ($replyTo !== null) {
                $mail->addReplyTo($replyTo);
            }

            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $result = $mail->send();

            $this->logger->channel('app')->info('Email sent', ['to' => $to, 'subject' => $subject]);
            return $result;
        } catch (MailException $e) {
            $this->logger->channel('app')->error('Email failed', [
                'to'    => $to,
                'error' => $mail->ErrorInfo,
            ]);
            throw new \RuntimeException('Email could not be sent: ' . $mail->ErrorInfo);
        }
    }

    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $cfg  = $this->config->all('mail');

        $fromAddress = $cfg['from']['address'] ?? 'noreply@hubercms.io';
        $fromName    = $cfg['from']['name'] ?? 'HuberCMS';

        $mail->setFrom($fromAddress, $fromName);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $driver = $cfg['driver'] ?? 'sendmail';

        if ($driver === 'smtp') {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'] ?? 'localhost';
            $mail->Port       = (int) ($cfg['port'] ?? 587);
            $mail->SMTPAuth   = !empty($cfg['username']);
            $mail->Username   = $cfg['username'] ?? '';
            $mail->Password   = $cfg['password'] ?? '';

            if (!empty($cfg['encryption'])) {
                $mail->SMTPSecure = $cfg['encryption'];
            }
        } else {
            $mail->isSendmail();
        }

        return $mail;
    }
}
