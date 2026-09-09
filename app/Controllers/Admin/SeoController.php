<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};

/** SeoController @package HuberCMS\Controllers\Admin */
final class SeoController extends Controller
{
    public function __construct(Session $session, private readonly Database $db) { parent::__construct($session); }

    public function index(Request $request): Response {
        $p = $this->db->prefix();
        $rows = $this->db->select("SELECT * FROM `{$p}settings` WHERE `group` = 'seo'");
        $seo = array_column($rows, 'value', 'key');
        return $this->view('admin.seo.index', ['title' => 'SEO', 'seo' => $seo, 'currentRoute' => 'admin.seo']);
    }

    public function update(Request $request): Response {
        $p = $this->db->prefix();
        $fields = ['meta_title_format', 'meta_description_default', 'robots_txt', 'google_analytics_id', 'google_search_console'];
        foreach ($fields as $key) {
            $val = strip_tags((string) $request->post($key, ''));
            $this->db->statement("INSERT INTO `{$p}settings` (`group`,`key`,`value`) VALUES ('seo',?,?) ON DUPLICATE KEY UPDATE value=?", [$key, $val, $val]);
        }
        $this->flashSuccess('SEO-Einstellungen gespeichert.');
        return $this->redirect('/admin/seo');
    }

    public function sitemap(Request $request): Response {
        $p = $this->db->prefix();
        $posts = $this->db->select("SELECT slug, updated_at FROM `{$p}posts` WHERE status='published' AND deleted_at IS NULL");
        $pages = $this->db->select("SELECT slug, updated_at FROM `{$p}pages` WHERE status='published' AND deleted_at IS NULL");
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= "<url><loc>{$baseUrl}/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>";
        foreach ($posts as $row) {
            $xml .= "<url><loc>{$baseUrl}/blog/{$row['slug']}</loc><lastmod>" . date('Y-m-d', strtotime($row['updated_at'])) . "</lastmod><priority>0.8</priority></url>";
        }
        foreach ($pages as $row) {
            $xml .= "<url><loc>{$baseUrl}/seite/{$row['slug']}</loc><lastmod>" . date('Y-m-d', strtotime($row['updated_at'])) . "</lastmod><priority>0.7</priority></url>";
        }
        $xml .= '</urlset>';
        return Response::html($xml)->setHeader('Content-Type', 'application/xml; charset=UTF-8');
    }
}
