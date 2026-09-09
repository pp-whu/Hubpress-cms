<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};

/** LogsController @package HuberCMS\Controllers\Admin */
final class LogsController extends Controller
{
    private const CHANNELS = ['app', 'auth', 'api', 'plugins', 'system'];

    public function __construct(Session $session) { parent::__construct($session); }

    public function index(Request $request): Response {
        return $this->view('admin.logs.index', ['title' => 'Logs', 'channels' => self::CHANNELS, 'currentRoute' => 'admin.logs']);
    }

    public function show(Request $request, string $channel): Response {
        $channel = preg_replace('/[^a-z]/', '', $channel);
        if (!in_array($channel, self::CHANNELS)) return $this->redirect('/admin/logs');
        $lines = $this->readLog($channel);
        return $this->view('admin.logs.show', ['title' => "Log: {$channel}", 'channel' => $channel, 'lines' => $lines, 'currentRoute' => 'admin.logs']);
    }

    private function readLog(string $channel): array {
        $path = STORAGE_PATH . "/Logs/{$channel}-" . date('Y-m-d') . '.log';
        if (!file_exists($path)) {
            $path = STORAGE_PATH . "/Logs/{$channel}.log";
        }
        if (!file_exists($path)) return [];
        $lines = array_reverse(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);
        return array_slice($lines, 0, 200);
    }
}
