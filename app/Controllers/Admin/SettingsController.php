<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};

/** SettingsController @package HuberCMS\Controllers\Admin */
final class SettingsController extends Controller
{
    public function __construct(Session $session, private readonly Database $db) { parent::__construct($session); }

    public function index(Request $request): Response {
        $p = $this->db->prefix();
        $rows = $this->db->select("SELECT * FROM `{$p}settings` ORDER BY `group`, `key`");
        $settings = [];
        foreach ($rows as $row) { $settings[$row['group']][$row['key']] = $row['value']; }
        return $this->view('admin.settings.index', ['title' => 'Einstellungen', 'settings' => $settings, 'currentRoute' => 'admin.settings']);
    }

    public function update(Request $request): Response {
        $p = $this->db->prefix();
        $group = preg_replace('/[^a-z0-9_]/', '', (string) $request->post('group', 'general'));
        foreach ($request->post() as $key => $value) {
            if ($key === '_token' || $key === 'group') continue;
            $key = preg_replace('/[^a-z0-9_]/', '', $key);
            $this->db->statement("INSERT INTO `{$p}settings` (`group`,`key`,`value`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE value=?", [$group, $key, $value, $value]);
        }
        $this->flashSuccess('Einstellungen gespeichert.');
        return $this->redirect('/admin/einstellungen');
    }

    public function system(Request $request): Response {
        return $this->view('admin.settings.system', [
            'title' => 'Systeminformationen',
            'info' => [
                'PHP Version'       => PHP_VERSION,
                'OS'                => PHP_OS,
                'Server Software'   => $_SERVER['SERVER_SOFTWARE'] ?? 'n/a',
                'Max Upload Size'   => ini_get('upload_max_filesize'),
                'Memory Limit'      => ini_get('memory_limit'),
                'Loaded Extensions' => implode(', ', get_loaded_extensions()),
            ],
            'currentRoute' => 'admin.settings',
        ]);
    }
}
