<?php

declare(strict_types=1);

namespace HuberCMS\Services;

use RuntimeException;
use ZipArchive;

final class UpdateService
{
    public function __construct(private readonly string $basePath)
    {
    }

    /** @return array{version:string, download_url:string} */
    public function latest(): array
    {
        $source = trim((string) ($_ENV['HUBERCMS_UPDATE_URL'] ?? ''));
        if ($source === '') {
            throw new RuntimeException('Keine Update-Quelle konfiguriert.');
        }
        if (strtolower((string) parse_url($source, PHP_URL_SCHEME)) !== 'https') {
            throw new RuntimeException('Die Update-Quelle muss HTTPS verwenden.');
        }

        $metadata = $this->requestJson($source);
        $version = ltrim((string) ($metadata['version'] ?? $metadata['tag_name'] ?? ''), 'v');
        $downloadUrl = (string) ($metadata['download_url'] ?? $metadata['zipball_url'] ?? '');

        if ($version === '' || $downloadUrl === '' || !filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Die Update-Quelle liefert keine gültige Version oder Download-URL.');
        }

        if (strtolower((string) parse_url($downloadUrl, PHP_URL_SCHEME)) !== 'https') {
            throw new RuntimeException('Updates sind nur über HTTPS erlaubt.');
        }

        return ['version' => $version, 'download_url' => $downloadUrl];
    }

    public function install(string $downloadUrl): string
    {
        $latest = $this->latest();
        if (!hash_equals($latest['download_url'], $downloadUrl)) {
            throw new RuntimeException('Die Update-Quelle hat sich geändert. Bitte erneut prüfen.');
        }

        $archive = tempnam(sys_get_temp_dir(), 'hubercms-update-');
        $extractPath = $archive . '-extract';
        if ($archive === false) {
            throw new RuntimeException('Temporäre Update-Datei konnte nicht erstellt werden.');
        }

        try {
            if (file_put_contents($archive, $this->request($downloadUrl)) === false) {
                throw new RuntimeException('Das Update-Archiv konnte nicht gespeichert werden.');
            }
            $zip = new ZipArchive();
            if ($zip->open($archive) !== true || !$zip->extractTo($extractPath)) {
                throw new RuntimeException('Das Update-Archiv konnte nicht entpackt werden.');
            }
            $sourcePath = $this->findRoot($extractPath);
            $this->copyTree($sourcePath, $this->basePath);
            return $latest['version'];
        } finally {
            @unlink($archive);
            $this->removeTree($extractPath);
        }
    }

    /** @return array<string, mixed> */
    private function requestJson(string $url): array
    {
        $data = json_decode($this->request($url), true);
        if (!is_array($data)) {
            throw new RuntimeException('Die Update-Quelle antwortet nicht mit gültigem JSON.');
        }
        return $data;
    }

    private function request(string $url): string
    {
        $headers = [
            'Accept: application/vnd.github+json',
            'User-Agent: HuberCMS-Updater',
        ];
        $token = trim((string) ($_ENV['HUBERCMS_GITHUB_TOKEN'] ?? ''));
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'timeout' => 15,
            'ignore_errors' => true,
            'header' => implode("\r\n", $headers) . "\r\n",
        ]]);
        $body = @file_get_contents($url, false, $context);
        $statusLine = $http_response_header[0] ?? '';
        if ($body === false || preg_match('/\s(\d{3})\s/', $statusLine, $matches) !== 1 || $matches[1] !== '200') {
            $status = isset($matches[1]) ? ' HTTP ' . $matches[1] : '';
            $hint = $status === ' HTTP 404'
                ? ' Prüfe, ob das Repository privat ist und ob ein Release veröffentlicht wurde.'
                : '';
            throw new RuntimeException('Die Update-Quelle liefert keine gültige Antwort (' . trim($url) . $status . ').' . $hint);
        }
        return $body;
    }

    private function findRoot(string $path): string
    {
        $entries = array_values(array_diff(scandir($path) ?: [], ['.', '..']));
        if (count($entries) === 1 && is_dir($path . '/' . $entries[0])) {
            return $path . '/' . $entries[0];
        }
        return $path;
    }

    private function copyTree(string $source, string $target): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($source) + 1);
            if ($relative === '.env' || str_starts_with($relative, 'app/Storage/')) {
                continue;
            }
            $destination = $target . '/' . $relative;
            if ($item->isDir()) {
                if (!is_dir($destination)) mkdir($destination, 0755, true);
            } else {
                if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0755, true);
                copy($item->getPathname(), $destination);
            }
        }
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        @rmdir($path);
    }
}