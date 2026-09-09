<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};

/**
 * ThemeEditorController — Direct theme file editing
 * @package HuberCMS\Controllers\Admin
 */
final class ThemeEditorController extends Controller
{
    public function __construct(Session $session, private readonly Database $db)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $activeTheme = $this->getActiveTheme();
        $themePath   = BASE_PATH . '/Themes/' . $activeTheme;

        if (!is_dir($themePath)) {
            // Fallback: show all available themes
            $available = glob(BASE_PATH . '/Themes/*/theme.php') ?: [];
            if (!empty($available)) {
                $activeTheme = basename(dirname($available[0]));
                $themePath   = BASE_PATH . '/Themes/' . $activeTheme;
            } else {
                return $this->view('admin.theme_editor.index', [
                    'title'        => 'Theme-Editor',
                    'activeTheme'  => $activeTheme,
                    'files'        => [],
                    'selectedFile' => '',
                    'content'      => '',
                    'currentRoute' => 'admin.theme_editor',
                ]);
            }
        }

        $files = $this->scanThemeFiles($themePath);

        $selectedFile = $request->query('file', '');
        $content      = '';

        if ($selectedFile !== '') {
            $fullPath = $this->resolveSafePath($themePath, $selectedFile);
            if ($fullPath !== null && file_exists($fullPath)) {
                $content = (string) file_get_contents($fullPath);
            }
        }

        return $this->view('admin.theme_editor.index', [
            'title'        => 'Theme-Editor',
            'activeTheme'  => $activeTheme,
            'files'        => $files,
            'selectedFile' => $selectedFile,
            'content'      => $content,
            'currentRoute' => 'admin.theme_editor',
        ]);
    }

    public function save(Request $request): Response
    {
        $activeTheme = $this->getActiveTheme();
        $themePath   = BASE_PATH . '/Themes/' . $activeTheme;
        $file        = (string) $request->post('file', '');
        $content     = (string) $request->post('content', '');

        $fullPath = $this->resolveSafePath($themePath, $file);

        if ($fullPath === null) {
            $this->flashError('Ungültiger Dateipfad.');
            return $this->redirect('/admin/theme-editor');
        }

        file_put_contents($fullPath, $content, LOCK_EX);

        $this->flashSuccess('Datei gespeichert.');
        return $this->redirect('/admin/theme-editor?file=' . urlencode($file));
    }

    // =========================================================
    // Private helpers
    // =========================================================

    private function getActiveTheme(): string
    {
        try {
            $p   = $this->db->prefix();
            $row = $this->db->selectOne(
                "SELECT value FROM `{$p}settings` WHERE `group` = 'general' AND `key` = 'active_theme' LIMIT 1"
            );
            return $row['value'] ?? 'default';
        } catch (\Throwable) {
            return 'default';
        }
    }

    /** @return array<int, array{name:string, path:string}> */
    private function scanThemeFiles(string $basePath): array
    {
        if (!is_dir($basePath)) {
            return [];
        }

        $files   = [];
        $allowed = ['php', 'css', 'js', 'json', 'txt', 'md'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $allowed, true)) continue;

            $relative = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $files[]  = ['name' => $relative, 'path' => $relative];
        }

        usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $files;
    }

    private function resolveSafePath(string $base, string $relative): ?string
    {
        $baseReal = realpath($base);
        if ($baseReal === false) {
            return null;
        }

        $relative = str_replace('\\', '/', (string) $relative);
        if (str_contains($relative, "\0") || $relative === '' || $relative === '.') {
            return null;
        }

        $segments = explode('/', trim($relative, "/\\"));
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        $candidate = $baseReal . '/' . implode('/', $segments);
        $candidate = str_replace(['//', '\\'], '/', $candidate);

        $parentReal = realpath(dirname($candidate));
        if ($parentReal === false) {
            return null;
        }

        $basePrefix = rtrim($baseReal, '/\\') . DIRECTORY_SEPARATOR;
        $parentPrefix = rtrim($parentReal, '/\\') . DIRECTORY_SEPARATOR;
        if ($parentPrefix !== $basePrefix && !str_starts_with($parentPrefix, $basePrefix)) {
            return null;
        }

        $allowed = ['php', 'css', 'js', 'json', 'txt', 'md'];
        $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        return $candidate;
    }
}
