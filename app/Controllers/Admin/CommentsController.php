<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};

/**
 * CommentsController — Admin comment management
 * @package HuberCMS\Controllers\Admin
 */
final class CommentsController extends Controller
{
    public function __construct(Session $session, private readonly Database $db)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $p            = $this->db->prefix();
        $filterStatus = $request->query('status', '');
        $search       = trim((string) $request->query('s', ''));

        $validStatuses = ['pending', 'approved', 'spam', 'trashed'];
        $where    = '1=1';
        $bindings = [];

        if ($filterStatus !== '' && in_array($filterStatus, $validStatuses, true)) {
            $where    .= " AND c.status = ?";
            $bindings[] = $filterStatus;
        }

        if ($search !== '') {
            $where    .= " AND (c.content LIKE ? OR c.author_name LIKE ? OR c.author_email LIKE ?)";
            $like      = '%' . $search . '%';
            $bindings  = array_merge($bindings, [$like, $like, $like]);
        }

        $comments = $this->db->select(
            "SELECT c.*,
                    p.title   AS post_title,
                    p.slug    AS post_slug,
                    u.username AS user_name
             FROM `{$p}comments` c
             LEFT JOIN `{$p}posts` p ON p.id = c.post_id
             LEFT JOIN `{$p}users` u ON u.id = c.user_id
             WHERE {$where}
             ORDER BY c.created_at DESC
             LIMIT 100",
            $bindings
        );

        // Count per status
        $counts = [];
        foreach (['pending', 'approved', 'spam', 'trashed'] as $s) {
            $row = $this->db->selectOne(
                "SELECT COUNT(*) AS c FROM `{$p}comments` WHERE status = ?", [$s]
            );
            $counts[$s] = (int) ($row['c'] ?? 0);
        }
        $counts['all'] = array_sum($counts);

        return $this->view('admin.comments.index', [
            'title'        => 'Kommentare',
            'comments'     => $comments,
            'filterStatus' => $filterStatus,
            'search'       => $search,
            'counts'       => $counts,
            'currentRoute' => 'admin.comments',
        ]);
    }

    public function approve(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement(
            "UPDATE `{$p}comments` SET status = 'approved' WHERE id = ?",
            [(int) $id]
        );
        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => true, 'status' => 'approved']);
        }
        $this->flashSuccess('Kommentar genehmigt.');
        return $this->back($request, '/admin/kommentare');
    }

    public function unapprove(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement(
            "UPDATE `{$p}comments` SET status = 'pending' WHERE id = ?",
            [(int) $id]
        );
        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => true, 'status' => 'pending']);
        }
        $this->flashSuccess('Kommentar auf "Ausstehend" gesetzt.');
        return $this->back($request, '/admin/kommentare');
    }

    public function spam(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement(
            "UPDATE `{$p}comments` SET status = 'spam' WHERE id = ?",
            [(int) $id]
        );
        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => true, 'status' => 'spam']);
        }
        $this->flashSuccess('Kommentar als Spam markiert.');
        return $this->back($request, '/admin/kommentare');
    }

    public function delete(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement(
            "UPDATE `{$p}comments` SET status = 'trashed' WHERE id = ?",
            [(int) $id]
        );
        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => true]);
        }
        $this->flashSuccess('Kommentar in den Papierkorb verschoben.');
        return $this->back($request, '/admin/kommentare');
    }

    public function restore(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement(
            "UPDATE `{$p}comments` SET status = 'approved' WHERE id = ?",
            [(int) $id]
        );
        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => true, 'status' => 'approved']);
        }
        $this->flashSuccess('Kommentar wiederhergestellt.');
        return $this->back($request, '/admin/kommentare');
    }
}
