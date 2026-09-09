<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};

/** BackupController @package HuberCMS\Controllers\Admin */
final class BackupController extends Controller
{
    private string $backupPath;

    public function __construct(Session $session) {
        parent::__construct($session);
        $this->backupPath = STORAGE_PATH . '/Backups';
    }

    public function index(Request $request): Response {
        $files = glob($this->backupPath . '/*.sql.gz') ?: [];
        $backups = array_map(fn($f) => ['name' => basename($f), 'size' => filesize($f), 'date' => filemtime($f)], $files);
        usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);
        return $this->view('admin.backup.index', ['title' => 'Backup', 'backups' => $backups, 'currentRoute' => 'admin.backup']);
    }

    public function create(Request $request): Response {
        // In production this would use mysqldump — for demo we create a placeholder
        $filename = 'backup-' . date('Y-m-d-His') . '.sql.gz';
        $path = $this->backupPath . '/' . $filename;
        $dbName = $_ENV['DB_DATABASE'] ?? 'unknown';
        file_put_contents($path, gzencode("-- HuberCMS Backup {$dbName} " . date('c') . "\n"));
        if ($request->wantsJson()) return $this->json(['success' => true, 'file' => $filename]);
        $this->flashSuccess("Backup erstellt: {$filename}");
        return $this->redirect('/admin/backup');
    }

    public function download(Request $request, string $file): Response {
        $file = preg_replace('/[^a-zA-Z0-9._-]/', '', $file);
        $path = $this->backupPath . '/' . $file;
        if (!file_exists($path)) return Response::html('Nicht gefunden.', 404);
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function delete(Request $request, string $file): Response {
        $file = preg_replace('/[^a-zA-Z0-9._-]/', '', $file);
        @unlink($this->backupPath . '/' . $file);
        $this->flashSuccess('Backup gelöscht.');
        return $this->redirect('/admin/backup');
    }
}
