<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Installer;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Core\Database;
use HuberCMS\Core\View;
use HuberCMS\Database\Migrations\CreateUsersTable;
use HuberCMS\Database\Migrations\CreateCmsCoreTables;
use HuberCMS\Enums\UserRole;

/**
 * InstallerController
 *
 * Multi-step installation wizard:
 *   Step 1 — System requirements check
 *   Step 2 — Database configuration
 *   Step 3 — Admin user creation
 *   Step 4 — Done, create install.lock
 *
 * @package HuberCMS\Controllers\Installer
 */
final class InstallerController extends Controller
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    /**
     * Renders the installation start page (requirements check).
     */
    public function index(Request $request): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect('/admin');
        }

        $checks = $this->runChecks();
        $allPassed = !in_array(false, array_column($checks, 'passed'), true);

        return $this->view('installer.index', [
            'title'     => 'HuberCMS installieren',
            'checks'    => $checks,
            'allPassed' => $allPassed,
        ]);
    }

    /**
     * Renders a specific installation step.
     */
    public function step(Request $request, string $step): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect('/admin');
        }

        return match ($step) {
            '2' => $this->view('installer.step-2', ['title' => 'Datenbank konfigurieren']),
            '3' => $this->view('installer.step-3', ['title' => 'Admin erstellen']),
            '4' => $this->view('installer.step-4', ['title' => 'Installation abgeschlossen']),
            default => $this->redirect('/install'),
        };
    }

    /**
     * Processes an installation step.
     */
    public function process(Request $request): Response
    {
        $currentStep = (int) $request->post('step', 1);

        return match ($currentStep) {
            2 => $this->processDatabase($request),
            3 => $this->processAdmin($request),
            default => $this->redirect('/install'),
        };
    }

    // =========================================================
    // Step 2: Database setup
    // =========================================================

    private function processDatabase(Request $request): Response
    {
        $host     = (string) $request->post('db_host', '127.0.0.1');
        $port     = (int) $request->post('db_port', 3306);
        $database = (string) $request->post('db_database', '');
        $username = (string) $request->post('db_username', '');
        $password = (string) $request->post('db_password', '');
        $prefix   = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $request->post('db_prefix', 'hcms_'));

        if (!$this->isAllowedDatabaseHost($host) || $port < 1 || $port > 65535) {
            $this->flashError('Dieser Datenbankserver ist für die Installation nicht freigegeben.');
            return $this->redirect('/install/step/2');
        }

        // Test connection
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\PDOException $e) {
            $this->flashError('Datenbankverbindung fehlgeschlagen. Bitte Zugangsdaten und Server prüfen.');
            return $this->redirect('/install/step/2');
        }

        // Store DB config temporarily in session
        $this->session->set('install_db', compact('host', 'port', 'database', 'username', 'password', 'prefix'));

        return $this->redirect('/install/step/3');
    }

    // =========================================================
    // Step 3: Admin creation + write config + run migrations
    // =========================================================

    private function processAdmin(Request $request): Response
    {
        $dbConfig = $this->session->get('install_db');

        if (!$dbConfig) {
            return $this->redirect('/install/step/2');
        }

        $adminUsername = htmlspecialchars((string) $request->post('admin_username', ''), ENT_QUOTES);
        $adminEmail    = strtolower(trim((string) $request->post('admin_email', '')));
        $adminPassword = (string) $request->post('admin_password', '');
        $siteName      = htmlspecialchars((string) $request->post('site_name', 'HuberCMS'), ENT_QUOTES);

        if (empty($adminUsername) || empty($adminEmail) || strlen($adminPassword) < 8) {
            $this->flashError('Bitte alle Felder ausfüllen. Passwort muss mindestens 8 Zeichen haben.');
            return $this->redirect('/install/step/3');
        }

        try {
            // Run migrations
            $db = $this->buildDatabase($dbConfig);
            (new CreateUsersTable($db))->up();
            (new CreateCmsCoreTables($db))->up();

            // Create admin user
            $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $prefix = $dbConfig['prefix'];

            $db->insert(
                "INSERT INTO `{$prefix}users` (username, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)",
                [$adminUsername, $adminEmail, $passwordHash, UserRole::SuperAdmin->value]
            );

            // Insert default settings
            $this->seedSettings($db, $siteName);

            // Generate app key
            $appKey = bin2hex(random_bytes(32));

            // Write .env / generated config
            $this->writeConfig($dbConfig, $appKey, $siteName);

            // Create install lock
            file_put_contents(PUBLIC_PATH . '/install.lock', date('Y-m-d H:i:s'));

            $this->session->forget('install_db');

        } catch (\Throwable $e) {
            $this->flashError('Fehler während der Installation. Bitte die Eingaben und Serverprotokolle prüfen.');
            return $this->redirect('/install/step/3');
        }

        return $this->redirect('/install/step/4');
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /** @param array<string, mixed> $dbConfig */
    private function buildDatabase(array $dbConfig): Database
    {
        // Temporarily override env for this request
        $_ENV['DB_HOST']     = $dbConfig['host'];
        $_ENV['DB_PORT']     = $dbConfig['port'];
        $_ENV['DB_DATABASE'] = $dbConfig['database'];
        $_ENV['DB_USERNAME'] = $dbConfig['username'];
        $_ENV['DB_PASSWORD'] = $dbConfig['password'];
        $_ENV['DB_PREFIX']   = $dbConfig['prefix'];

        // Minimal config object for the database
        $config = new \HuberCMS\Core\Config(BASE_PATH);
        return new Database($config);
    }

    private function isAllowedDatabaseHost(string $host): bool
    {
        $host = strtolower(trim($host, " \t\n\r\0\x0B[]"));

        if ($host === '' || preg_match('/[^a-z0-9.:-]/', $host) === 1) {
            return false;
        }

        $configuredHosts = $_ENV['INSTALL_DB_ALLOWED_HOSTS'] ?? 'db,localhost,127.0.0.1,::1';
        $allowedHosts = array_map(
            static fn(string $value): string => strtolower(trim($value, " \t\n\r\0\x0B[]")),
            explode(',', $configuredHosts)
        );

        return in_array($host, $allowedHosts, true);
    }

    /**
     * @param array<string, mixed> $dbConfig
     */
    private function writeConfig(array $dbConfig, string $appKey, string $siteName): void
    {
        $envContent = "# Generated by HuberCMS Installer — " . date('Y-m-d H:i:s') . "\n"
            . "APP_NAME=\"{$siteName}\"\n"
            . "APP_KEY={$appKey}\n"
            . "APP_ENV=production\n"
            . "APP_DEBUG=false\n"
            . "APP_URL=\n"
            . "DB_HOST={$dbConfig['host']}\n"
            . "DB_PORT={$dbConfig['port']}\n"
            . "DB_DATABASE={$dbConfig['database']}\n"
            . "DB_USERNAME={$dbConfig['username']}\n"
            . "DB_PASSWORD={$dbConfig['password']}\n"
            . "DB_PREFIX={$dbConfig['prefix']}\n";

        file_put_contents(BASE_PATH . '/.env', $envContent, LOCK_EX);
    }

    private function seedSettings(Database $db, string $siteName): void
    {
        $p = $db->prefix();
        $settings = [
            ['general', 'site_name',          $siteName,     'string'],
            ['general', 'site_description',   '',            'string'],
            ['general', 'admin_email',         '',            'string'],
            ['general', 'registration_open',   '1',           'bool'],
            ['general', 'active_theme',        'default',     'string'],
            ['seo',     'meta_title_format',   '%s | ' . $siteName, 'string'],
            ['seo',     'robots_txt',          "User-agent: *\nAllow: /", 'string'],
        ];

        foreach ($settings as [$group, $key, $value, $type]) {
            $db->statement(
                "INSERT IGNORE INTO `{$p}settings` (`group`, `key`, `value`, `type`) VALUES (?, ?, ?, ?)",
                [$group, $key, $value, $type]
            );
        }
    }

    /**
     * @return array<int, array{label:string, passed:bool, detail:string}>
     */
    private function runChecks(): array
    {
        return [
            [
                'label'  => 'PHP 8.4+',
                'passed' => PHP_VERSION_ID >= 80400,
                'detail' => 'PHP ' . PHP_VERSION,
            ],
            [
                'label'  => 'PDO MySQL',
                'passed' => extension_loaded('pdo_mysql'),
                'detail' => extension_loaded('pdo_mysql') ? 'Verfügbar' : 'Nicht installiert',
            ],
            [
                'label'  => 'GD / Image',
                'passed' => extension_loaded('gd'),
                'detail' => extension_loaded('gd') ? 'Verfügbar' : 'Nicht installiert',
            ],
            [
                'label'  => 'JSON',
                'passed' => extension_loaded('json'),
                'detail' => 'OK',
            ],
            [
                'label'  => 'mbstring',
                'passed' => extension_loaded('mbstring'),
                'detail' => extension_loaded('mbstring') ? 'OK' : 'Fehlend',
            ],
            [
                'label'  => 'OpenSSL',
                'passed' => extension_loaded('openssl'),
                'detail' => extension_loaded('openssl') ? 'OK' : 'Fehlend',
            ],
            [
                'label'  => 'Schreibrechte storage/',
                'passed' => is_writable(STORAGE_PATH),
                'detail' => is_writable(STORAGE_PATH) ? 'OK' : 'Kein Schreibzugriff',
            ],
            [
                'label'  => 'Composer vendor/',
                'passed' => is_dir(BASE_PATH . '/vendor'),
                'detail' => is_dir(BASE_PATH . '/vendor') ? 'OK' : 'composer install ausführen',
            ],
        ];
    }

    private function isInstalled(): bool
    {
        return file_exists(PUBLIC_PATH . '/install.lock');
    }
}
