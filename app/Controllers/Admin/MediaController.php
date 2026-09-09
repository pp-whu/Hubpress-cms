<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Services\MediaService;
use HuberCMS\Core\Database;

/**
 * MediaController — Admin media library
 *
 * @package HuberCMS\Controllers\Admin
 */
final class MediaController extends Controller
{
    public function __construct(
        Session                        $session,
        private readonly MediaService $mediaService,
        private readonly Database      $db
    ) {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $p    = $this->db->prefix();
        $type = $request->query('type', '');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 60;

        $where    = "WHERE 1=1";
        $bindings = [];

        if ($type !== '' && in_array($type, ['image','video','audio','document','archive'], true)) {
            $where    .= " AND type = ?";
            $bindings[] = $type;
        }

        $total = (int) ($this->db->selectOne(
            "SELECT COUNT(*) AS c FROM `{$p}media` {$where}", $bindings
        )['c'] ?? 0);

        $media = $this->db->select(
            "SELECT * FROM `{$p}media` {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $bindings
        );

        return $this->view('admin.media.index', [
            'title'        => 'Medienverwaltung',
            'media'        => $media,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $perPage,
            'filterType'   => $type,
            'currentRoute' => 'admin.media',
        ]);
    }

    public function upload(Request $request): Response
    {
        $file   = $request->file('file');
        $userId = (int) ($this->session->get('auth_user')['id'] ?? 0);

        if ($file === null) {
            return $this->json(['success' => false, 'error' => 'Keine Datei hochgeladen.'], 400);
        }

        try {
            $media = $this->mediaService->upload($file, $userId);
            return $this->json([
                'success' => true,
                'media'   => $media->toArray(),
            ]);
        } catch (\Throwable) {
            return $this->json(['success' => false, 'error' => 'Datei konnte nicht verarbeitet werden.'], 422);
        }
    }

    public function delete(Request $request, string $id): Response
    {
        $deleted = $this->mediaService->delete((int) $id);

        if ($request->wantsJson() || $request->isAjax()) {
            return $this->json(['success' => $deleted]);
        }

        $this->flashSuccess('Medium gelöscht.');
        return $this->redirect('/admin/medien');
    }

    public function updateAlt(Request $request, string $id): Response
    {
        $p       = $this->db->prefix();
        $alt     = strip_tags((string) $request->post('alt_text', ''));
        $caption = strip_tags((string) $request->post('caption', ''));

        $this->db->statement(
            "UPDATE `{$p}media` SET alt_text = ?, caption = ? WHERE id = ?",
            [$alt, $caption, (int) $id]
        );

        return $this->json(['success' => true]);
    }
}
