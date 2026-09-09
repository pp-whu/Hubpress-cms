<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Core\Database;
use HuberCMS\Core\Cache;

/**
 * DashboardController
 *
 * Admin panel dashboard — displays aggregated statistics,
 * recent activity and system health information.
 *
 * @package HuberCMS\Controllers\Admin
 */
final class DashboardController extends Controller
{
    public function __construct(
        Session                  $session,
        private readonly Database $db,
        private readonly Cache   $cache
    ) {
        parent::__construct($session);
    }

    /**
     * Renders the admin dashboard.
     */
    public function index(Request $request): Response
    {
        $stats = $this->cache->remember('admin.dashboard.stats', function () {
            return $this->loadStats();
        }, 300);

        $recent = $this->loadRecentActivity();

        return $this->view('admin.dashboard.index', [
            'title'          => 'Dashboard',
            'stats'          => $stats,
            'recentPosts'    => $recent['posts'],
            'recentUsers'    => $recent['users'],
            'recentComments' => $recent['comments'],
            'systemInfo'     => $this->systemInfo(),
            'updateConfigured' => trim((string) ($_ENV['HUBERCMS_UPDATE_URL'] ?? '')) !== '',
        ]);
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Loads aggregated CMS statistics.
     *
     * @return array<string, int>
     */
    private function loadStats(): array
    {
        $p = $this->db->prefix();

        return [
            'total_posts'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}posts` WHERE deleted_at IS NULL")['c'] ?? 0),
            'published_posts' => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}posts` WHERE status = 'published' AND deleted_at IS NULL")['c'] ?? 0),
            'total_pages'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}pages` WHERE deleted_at IS NULL")['c'] ?? 0),
            'total_users'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}users`")['c'] ?? 0),
            'active_users'   => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}users` WHERE is_active = 1")['c'] ?? 0),
            'total_media'    => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}media`")['c'] ?? 0),
            'pending_comments' => (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM `{$p}comments` WHERE status = 'pending'")['c'] ?? 0),
        ];
    }

    /**
     * Loads recent activity for the dashboard feed.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function loadRecentActivity(): array
    {
        $p = $this->db->prefix();

        return [
            'posts' => $this->db->select(
                "SELECT p.id, p.title, p.status, p.created_at, u.username AS author
                 FROM `{$p}posts` p
                 LEFT JOIN `{$p}users` u ON u.id = p.author_id
                 WHERE p.deleted_at IS NULL
                 ORDER BY p.created_at DESC LIMIT 5"
            ),
            'users' => $this->db->select(
                "SELECT id, username, email, role, created_at
                 FROM `{$p}users`
                 ORDER BY created_at DESC LIMIT 5"
            ),
            'comments' => $this->db->select(
                "SELECT c.id, c.content, c.status, c.created_at,
                        COALESCE(c.author_name, u.username, 'Anonym') AS author,
                        p.title AS post_title
                 FROM `{$p}comments` c
                 LEFT JOIN `{$p}users` u ON u.id = c.user_id
                 LEFT JOIN `{$p}posts` p ON p.id = c.post_id
                 ORDER BY c.created_at DESC LIMIT 5"
            ),
        ];
    }

    /**
     * Returns server/system info for the dashboard.
     *
     * @return array<string, string>
     */
    private function systemInfo(): array
    {
        return [
            'php_version'    => PHP_VERSION,
            'cms_version'    => HUBERCMS_VERSION,
            'server_os'      => PHP_OS,
            'memory_limit'   => ini_get('memory_limit') ?: '128M',
            'memory_usage'   => round(memory_get_usage(true) / 1048576, 1) . ' MB',
            'peak_memory'    => round(memory_get_peak_usage(true) / 1048576, 1) . ' MB',
            'disk_free'      => $this->formatBytes(disk_free_space('/') ?: 0),
            'upload_max'     => ini_get('upload_max_filesize') ?: '2M',
            'load_time'      => round((microtime(true) - START_TIME) * 1000) . ' ms',
        ];
    }

    private function formatBytes(float $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
        if ($bytes >= 1048576)   return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1024, 1) . ' KB';
    }
}
