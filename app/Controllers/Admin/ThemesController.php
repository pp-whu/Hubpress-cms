<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database, ThemeLoader};

/** ThemesController @package HuberCMS\Controllers\Admin */
final class ThemesController extends Controller
{
    public function __construct(Session $session, private readonly ThemeLoader $themes, private readonly Database $db) { parent::__construct($session); }

    public function index(Request $request): Response {
        return $this->view('admin.themes.index', ['title' => 'Themes', 'themes' => $this->themes->discover(), 'currentRoute' => 'admin.themes']);
    }

    public function activate(Request $request, string $slug): Response {
        $p = $this->db->prefix();
        $safeslug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
        $this->db->statement("INSERT INTO `{$p}settings` (`group`,`key`,`value`) VALUES ('general','active_theme',?) ON DUPLICATE KEY UPDATE value=?", [$safeslug, $safeslug]);
        $this->flashSuccess("Theme '{$safeslug}' aktiviert.");
        return $this->redirect('/admin/themes');
    }
}
