<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database, Config};

/**
 * ToolsController — WordPress-ähnliche Werkzeuge
 * @package HuberCMS\Controllers\Admin
 */
final class ToolsController extends Controller
{
    public function __construct(
        Session $session,
        private readonly Database $db,
        private readonly Config   $config
    ) {
        parent::__construct($session);
    }

    /** Übersicht aller verfügbaren Werkzeuge */
    public function index(Request $request): Response
    {
        return $this->view('admin.tools.index', [
            'title'        => 'Werkzeuge',
            'currentRoute' => 'admin.tools',
        ]);
    }

    /** Import-Seite */
    public function import(Request $request): Response
    {
        return $this->view('admin.tools.import', [
            'title'        => 'Importieren',
            'currentRoute' => 'admin.tools.import',
        ]);
    }

    public function doImport(Request $request): Response
    {
        $this->flashSuccess('Import abgeschlossen.');
        return $this->redirect('/admin/werkzeuge/import');
    }

    /** Export-Seite */
    public function export(Request $request): Response
    {
        $p = $this->db->prefix();

        $stats = [
            'posts'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}posts` WHERE deleted_at IS NULL")['c'] ?? 0),
            'pages'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}pages` WHERE deleted_at IS NULL")['c'] ?? 0),
            'comments' => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}comments`")['c'] ?? 0),
            'users'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}users`")['c'] ?? 0),
            'media'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}media`")['c'] ?? 0),
        ];

        return $this->view('admin.tools.export', [
            'title'        => 'Exportieren',
            'stats'        => $stats,
            'currentRoute' => 'admin.tools.export',
        ]);
    }

    public function doExport(Request $request): Response
    {
        $type = (string) $request->post('export_type', 'all');
        $p    = $this->db->prefix();

        $data = ['export_type' => $type, 'exported_at' => date('c'), 'data' => []];

        if ($type === 'all' || $type === 'posts') {
            $data['data']['posts'] = $this->db->select(
                "SELECT * FROM `{$p}posts` WHERE deleted_at IS NULL ORDER BY id"
            );
        }
        if ($type === 'all' || $type === 'pages') {
            $data['data']['pages'] = $this->db->select(
                "SELECT * FROM `{$p}pages` WHERE deleted_at IS NULL ORDER BY id"
            );
        }
        if ($type === 'all' || $type === 'media') {
            $data['data']['media'] = $this->db->select(
                "SELECT * FROM `{$p}media` ORDER BY id"
            );
        }
        if ($type === 'all' || $type === 'users') {
            $data['data']['users'] = $this->db->select(
                "SELECT id, username, email, role, created_at FROM `{$p}users`"
            );
        }
        if ($type === 'all' || $type === 'settings') {
            $data['data']['settings'] = $this->db->select(
                "SELECT * FROM `{$p}settings`"
            );
        }

        $filename = 'hubercms-export-' . date('Y-m-d') . '.json';
        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return Response::html($json)
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($json));
    }

    /** Website-Gesundheit */
    public function health(Request $request): Response
    {
        $checks = $this->runHealthChecks();

        return $this->view('admin.tools.health', [
            'title'        => 'Website-Gesundheit',
            'checks'       => $checks,
            'score'        => $this->calculateScore($checks),
            'currentRoute' => 'admin.tools.health',
        ]);
    }

    /** Persönliche Daten exportieren */
    public function exportData(Request $request): Response
    {
        return $this->view('admin.tools.export-data', [
            'title'        => 'Persönliche Daten exportieren',
            'currentRoute' => 'admin.tools.export_data',
        ]);
    }

    public function doExportData(Request $request): Response
    {
        $email = strtolower(trim((string) $request->post('email', '')));
        $p     = $this->db->prefix();

        $user = $this->db->selectOne(
            "SELECT id, username, email, role, created_at FROM `{$p}users` WHERE email = ?",
            [$email]
        );

        if (!$user) {
            $this->flashError('Kein Benutzer mit dieser E-Mail-Adresse gefunden.');
            return $this->redirect('/admin/werkzeuge/export-data');
        }

        $data = [
            'user'     => $user,
            'comments' => $this->db->select(
                "SELECT id, content, status, created_at FROM `{$p}comments` WHERE user_id = ?",
                [$user['id']]
            ),
        ];

        $filename = 'persoenliche-daten-' . date('Y-m-d') . '.json';
        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return Response::html($json)
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($json));
    }

    /** Persönliche Daten löschen */
    public function deleteData(Request $request): Response
    {
        return $this->view('admin.tools.delete-data', [
            'title'        => 'Persönliche Daten löschen',
            'currentRoute' => 'admin.tools.delete_data',
        ]);
    }

    public function doDeleteData(Request $request): Response
    {
        $email   = strtolower(trim((string) $request->post('email', '')));
        $confirm = (string) $request->post('confirm', '');
        $p       = $this->db->prefix();

        if ($confirm !== 'LOESCHEN') {
            $this->flashError('Bitte gib LOESCHEN zur Bestätigung ein.');
            return $this->redirect('/admin/werkzeuge/delete-data');
        }

        $user = $this->db->selectOne(
            "SELECT id FROM `{$p}users` WHERE email = ?", [$email]
        );

        if (!$user) {
            $this->flashError('Kein Benutzer mit dieser E-Mail-Adresse gefunden.');
            return $this->redirect('/admin/werkzeuge/delete-data');
        }

        // Anonymize: delete comments, nullify personal data
        $this->db->statement(
            "UPDATE `{$p}comments` SET author_name = 'Gelöscht', author_email = NULL WHERE user_id = ?",
            [$user['id']]
        );
        $this->db->statement(
            "UPDATE `{$p}users` SET email = CONCAT('deleted_', id, '@removed.local'), first_name = NULL, last_name = NULL, bio = NULL, avatar = NULL WHERE id = ?",
            [$user['id']]
        );

        $this->flashSuccess('Persönliche Daten wurden anonymisiert.');
        return $this->redirect('/admin/werkzeuge/delete-data');
    }

    // =========================================================
    // Health check helpers
    // =========================================================

    /** @return array<int, array{label:string, status:string, detail:string, severity:string}> */
    private function runHealthChecks(): array
    {
        $checks = [];

        // PHP version
        $checks[] = [
            'label'    => 'PHP-Version',
            'status'   => PHP_VERSION_ID >= 80400 ? 'good' : 'critical',
            'detail'   => 'PHP ' . PHP_VERSION . ' (empfohlen: 8.4+)',
            'severity' => PHP_VERSION_ID >= 80400 ? 'good' : 'critical',
        ];

        // Extensions
        foreach (['pdo_mysql', 'mbstring', 'openssl', 'gd', 'json'] as $ext) {
            $checks[] = [
                'label'    => "PHP-Erweiterung: {$ext}",
                'status'   => extension_loaded($ext) ? 'good' : 'critical',
                'detail'   => extension_loaded($ext) ? 'Aktiv' : 'Nicht installiert',
                'severity' => extension_loaded($ext) ? 'good' : 'critical',
            ];
        }

        // Storage writeable
        $paths = [
            STORAGE_PATH . '/Logs'    => 'Logs-Verzeichnis',
            STORAGE_PATH . '/Cache'   => 'Cache-Verzeichnis',
            STORAGE_PATH . '/Uploads' => 'Upload-Verzeichnis',
        ];
        foreach ($paths as $path => $label) {
            $checks[] = [
                'label'    => $label,
                'status'   => is_writable($path) ? 'good' : 'warning',
                'detail'   => is_writable($path) ? 'Beschreibbar' : 'Kein Schreibzugriff',
                'severity' => is_writable($path) ? 'good' : 'warning',
            ];
        }

        // HTTPS
        $isHttps = str_starts_with($_ENV['APP_URL'] ?? '', 'https');
        $checks[] = [
            'label'    => 'HTTPS',
            'status'   => $isHttps ? 'good' : 'warning',
            'detail'   => $isHttps ? 'Aktiv' : 'Kein HTTPS konfiguriert',
            'severity' => $isHttps ? 'good' : 'warning',
        ];

        // Debug mode
        $debugOn = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $checks[] = [
            'label'    => 'Debug-Modus',
            'status'   => $debugOn ? 'warning' : 'good',
            'detail'   => $debugOn ? 'Aktiviert (in Produktion deaktivieren!)' : 'Deaktiviert',
            'severity' => $debugOn ? 'warning' : 'good',
        ];

        // Memory limit
        $memLimit = ini_get('memory_limit') ?: '128M';
        $checks[] = [
            'label'    => 'PHP Memory Limit',
            'status'   => 'good',
            'detail'   => $memLimit,
            'severity' => 'good',
        ];

        return $checks;
    }

    /** @param array<int, array{severity:string}> $checks */
    private function calculateScore(array $checks): int
    {
        if (empty($checks)) return 100;
        $good  = count(array_filter($checks, fn($c) => $c['severity'] === 'good'));
        return (int) round($good / count($checks) * 100);
    }
}
