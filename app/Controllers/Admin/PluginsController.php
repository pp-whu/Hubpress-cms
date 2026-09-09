<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, PluginLoader, Database};

/** PluginsController @package HuberCMS\Controllers\Admin */
final class PluginsController extends Controller
{
    public function __construct(Session $session, private readonly PluginLoader $plugins, private readonly Database $db) { parent::__construct($session); }

    public function index(Request $request): Response {
        return $this->view('admin.plugins.index', ['title' => 'Plugins', 'plugins' => $this->plugins->discover(), 'currentRoute' => 'admin.plugins']);
    }

    public function activate(Request $request, string $slug): Response {
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
        $active = $this->getActive(); $active[] = $slug; $active = array_unique($active);
        $this->saveActive($active);
        $this->flashSuccess("Plugin '{$slug}' aktiviert.");
        return $this->redirect('/admin/plugins');
    }

    public function deactivate(Request $request, string $slug): Response {
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
        $active = array_filter($this->getActive(), fn($s) => $s !== $slug);
        $this->saveActive(array_values($active));
        $this->flashSuccess("Plugin '{$slug}' deaktiviert.");
        return $this->redirect('/admin/plugins');
    }

    private function getActive(): array {
        $p = $this->db->prefix();
        $row = $this->db->selectOne("SELECT value FROM `{$p}settings` WHERE `key` = 'active_plugins' LIMIT 1");
        return json_decode($row['value'] ?? '[]', true) ?? [];
    }

    private function saveActive(array $active): void {
        $p = $this->db->prefix();
        $json = json_encode($active);
        $this->db->statement("INSERT INTO `{$p}settings` (`group`,`key`,`value`) VALUES ('general','active_plugins',?) ON DUPLICATE KEY UPDATE value=?", [$json, $json]);
    }
}
