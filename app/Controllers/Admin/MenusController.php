<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};

/** MenusController @package HuberCMS\Controllers\Admin */
final class MenusController extends Controller
{
    public function __construct(Session $session, private readonly Database $db) { parent::__construct($session); }

    public function index(Request $request): Response {
        $p = $this->db->prefix();
        $menus = $this->db->select("SELECT * FROM `{$p}menus` ORDER BY name");
        return $this->view('admin.menus.index', ['title' => 'Menüs', 'menus' => $menus, 'currentRoute' => 'admin.menus']);
    }

    public function store(Request $request): Response {
        $p = $this->db->prefix();
        $name = trim(strip_tags((string) $request->post('name', '')));

        if ($name === '') {
            $this->flashError('Bitte einen Menü-Namen eingeben.');
            return $this->redirect('/admin/menues');
        }

        $slug = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? ''), '-');
        if ($slug === '') {
            $slug = 'menu';
        }

        // Ensure slug uniqueness
        $base = $slug;
        $i = 1;
        while ($this->db->select("SELECT id FROM `{$p}menus` WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . (++$i);
        }

        $this->db->insert("INSERT INTO `{$p}menus` (name, slug) VALUES (?, ?)", [$name, $slug]);
        $this->flashSuccess('Menü erstellt.');
        return $this->redirect('/admin/menues');
    }

    public function update(Request $request, string $id): Response {
        $p        = $this->db->prefix();
        $name     = strip_tags((string) $request->post('name', ''));
        $location = strip_tags((string) $request->post('location', ''));
        $this->db->statement(
            "UPDATE `{$p}menus` SET name = ?, location = NULLIF(?, '') WHERE id = ?",
            [$name, $location, (int) $id]
        );
        $this->flashSuccess('Menü gespeichert.');
        return $this->redirect('/admin/menues');
    }

    public function delete(Request $request, string $id): Response {
        $p = $this->db->prefix();
        $this->db->statement("DELETE FROM `{$p}menu_items` WHERE menu_id = ?", [(int) $id]);
        $this->db->statement("DELETE FROM `{$p}menus` WHERE id = ?", [(int) $id]);
        $this->flashSuccess('Menü gelöscht.');
        return $this->redirect('/admin/menues');
    }

    public function items(Request $request, string $id): Response {
        $p = $this->db->prefix();
        $items = $this->db->select(
            "SELECT * FROM `{$p}menu_items` WHERE menu_id = ? ORDER BY sort_order, id",
            [(int) $id]
        );
        return $this->json(['success' => true, 'items' => $items]);
    }

    public function addItem(Request $request, string $id): Response {
        $p     = $this->db->prefix();
        $data  = $request->all();
        $label = strip_tags((string) ($data['label'] ?? $request->post('label', '')));
        $url   = strip_tags((string) ($data['url']   ?? $request->post('url', '')));
        $icon  = strip_tags((string) ($data['icon']  ?? $request->post('icon', '')));

        if (empty($label) || empty($url)) {
            return $this->json(['success' => false, 'error' => 'Label und URL erforderlich.'], 422);
        }

        if (!$this->isSafeUrl($url)) {
            return $this->json(['success' => false, 'error' => 'Ungültige URL.'], 422);
        }

        $itemId = $this->db->insert(
            "INSERT INTO `{$p}menu_items` (menu_id, label, url, target, icon, sort_order) VALUES (?, ?, ?, '_self', ?, 0)",
            [(int) $id, $label, $url, $icon ?: null]
        );

        return $this->json(['success' => true, 'id' => $itemId, 'label' => $label, 'url' => $url, 'icon' => $icon]);
    }

    private function isSafeUrl(string $url): bool
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1 || str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }

    public function removeItem(Request $request, string $id): Response {
        $p = $this->db->prefix();
        $this->db->statement("DELETE FROM `{$p}menu_items` WHERE id = ?", [(int) $id]);
        return $this->json(['success' => true]);
    }
}
