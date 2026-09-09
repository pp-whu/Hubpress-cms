<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};
use HuberCMS\Models\Page;
use HuberCMS\Enums\PostStatus;

/**
 * PagesController — Admin static page management
 * @package HuberCMS\Controllers\Admin
 */
final class PagesController extends Controller
{
    public function __construct(Session $session, private readonly Database $db)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $p            = $this->db->prefix();
        $filterStatus = $request->query('status', '');

        $where    = "deleted_at IS NULL";
        $bindings = [];

        if ($filterStatus !== '' && in_array($filterStatus, PostStatus::values(), true)) {
            $where    .= " AND status = ?";
            $bindings[] = $filterStatus;
        }

        $pages = $this->db->select(
            "SELECT * FROM `{$p}pages` WHERE {$where} ORDER BY sort_order, title",
            $bindings
        );

        return $this->view('admin.pages.index', [
            'title'        => 'Seiten',
            'pages'        => $pages,
            'filterStatus' => $filterStatus,
            'currentRoute' => 'admin.pages',
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->view('admin.pages.form', [
            'title'        => 'Seite erstellen',
            'page'         => null,
            'statuses'     => PostStatus::cases(),
            'currentRoute' => 'admin.pages',
        ]);
    }

    public function store(Request $request): Response
    {
        $authorId     = (int) ($this->session->get('auth_user')['id'] ?? 0);
        $title        = strip_tags((string) $request->post('title', ''));
        $statusAction = (string) $request->post('status_action', '');

        $status = match ($statusAction) {
            'publish' => PostStatus::Published->value,
            'draft'   => PostStatus::Draft->value,
            default   => (string) $request->post('status', PostStatus::Draft->value),
        };

        if (empty($title)) {
            $this->flashError('Titel ist erforderlich.');
            return $this->redirect('/admin/seiten/neu');
        }

        $slug = $this->generateUniqueSlug($title);

        Page::create([
            'title'            => $title,
            'slug'             => $request->post('slug') ?: $slug,
            'content'          => (string) $request->post('content', ''),
            'status'           => $status,
            'author_id'        => $authorId,
            'template'         => (string) $request->post('template', 'default'),
            'sort_order'       => (int) $request->post('sort_order', 0),
            'show_in_menu'     => $request->post('show_in_menu') ? 1 : 0,
            'meta_title'       => strip_tags((string) $request->post('meta_title', '')),
            'meta_description' => strip_tags((string) $request->post('meta_description', '')),
            'published_at'     => $status === PostStatus::Published->value ? date('Y-m-d H:i:s') : null,
        ]);

        $this->flashSuccess('Seite erfolgreich erstellt.');
        return $this->redirect('/admin/seiten');
    }

    public function edit(Request $request, string $id): Response
    {
        $page = Page::find((int) $id);

        if ($page === null) {
            $this->flashError('Seite nicht gefunden.');
            return $this->redirect('/admin/seiten');
        }

        return $this->view('admin.pages.form', [
            'title'        => 'Seite bearbeiten',
            'page'         => $page,
            'statuses'     => PostStatus::cases(),
            'currentRoute' => 'admin.pages',
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $page = Page::find((int) $id);

        if ($page === null) {
            $this->flashError('Seite nicht gefunden.');
            return $this->redirect('/admin/seiten');
        }

        $statusAction = (string) $request->post('status_action', '');
        $status = match ($statusAction) {
            'publish' => PostStatus::Published->value,
            'draft'   => PostStatus::Draft->value,
            default   => (string) $request->post('status', $page->getAttribute('status')),
        };

        if ($status === PostStatus::Published->value && !$page->getAttribute('published_at')) {
            $page->setAttribute('published_at', date('Y-m-d H:i:s'));
        }

        $page->setAttribute('title',            strip_tags((string) $request->post('title', '')));
        $page->setAttribute('content',          (string) $request->post('content', ''));
        $page->setAttribute('status',           $status);
        $page->setAttribute('template',         (string) $request->post('template', 'default'));
        $page->setAttribute('sort_order',       (int) $request->post('sort_order', 0));
        $page->setAttribute('show_in_menu',     $request->post('show_in_menu') ? 1 : 0);
        $page->setAttribute('meta_title',       strip_tags((string) $request->post('meta_title', '')));
        $page->setAttribute('meta_description', strip_tags((string) $request->post('meta_description', '')));

        if ($request->post('slug')) {
            $page->setAttribute('slug', (string) $request->post('slug'));
        }

        $page->save();

        $this->flashSuccess('Seite gespeichert.');
        return $this->redirect('/admin/seiten/' . $id . '/bearbeiten');
    }

    public function delete(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement(
            "UPDATE `{$p}pages` SET deleted_at = NOW(), status = 'trashed' WHERE id = ?",
            [(int) $id]
        );

        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => true]);
        }

        $this->flashSuccess('Seite in den Papierkorb verschoben.');
        return $this->redirect('/admin/seiten');
    }

    private function generateUniqueSlug(string $title): string
    {
        $umlauts = ['ä'=>'ae','ö'=>'oe','ü'=>'ue','Ä'=>'ae','Ö'=>'oe','Ü'=>'ue','ß'=>'ss'];
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr($title, $umlauts))) ?? 'seite', '-');
        $base = mb_substr($base, 0, 80);
        $slug = $base;
        $p    = $this->db->prefix();
        $i    = 1;

        while ($this->db->selectOne("SELECT id FROM `{$p}pages` WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
