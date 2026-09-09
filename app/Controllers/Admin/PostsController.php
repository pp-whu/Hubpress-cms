<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};
use HuberCMS\Models\Post;
use HuberCMS\Enums\PostStatus;
use HuberCMS\Controllers\ValidationException;

/**
 * PostsController — Admin blog post management
 * @package HuberCMS\Controllers\Admin
 */
final class PostsController extends Controller
{
    public function __construct(Session $session, private readonly Database $db)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $p = $this->db->prefix();
        $posts = $this->db->select(
            "SELECT p.*, u.username AS author_name FROM `{$p}posts` p
             LEFT JOIN `{$p}users` u ON u.id = p.author_id
             WHERE p.deleted_at IS NULL ORDER BY p.created_at DESC LIMIT 50"
        );
        return $this->view('admin.posts.index', [
            'title' => 'Beiträge', 'posts' => $posts,
            'statuses' => PostStatus::cases(), 'currentRoute' => 'admin.posts',
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->view('admin.posts.form', [
            'title' => 'Beitrag erstellen', 'post' => null,
            'statuses' => PostStatus::cases(), 'currentRoute' => 'admin.posts',
        ]);
    }

    public function store(Request $request): Response
    {
        $authorId = (int) ($this->session->get('auth_user')['id'] ?? 0);
        $slug = $this->generateSlug((string) $request->post('title', ''));

        Post::create([
            'title'            => strip_tags((string) $request->post('title', '')),
            'slug'             => $slug,
            'content'          => (string) $request->post('content', ''),
            'excerpt'          => strip_tags((string) $request->post('excerpt', '')),
            'status'           => (string) $request->post('status', PostStatus::Draft->value),
            'author_id'        => $authorId,
            'meta_title'       => strip_tags((string) $request->post('meta_title', '')),
            'meta_description' => strip_tags((string) $request->post('meta_description', '')),
            'allow_comments'   => (bool) $request->post('allow_comments', 1),
        ]);

        $this->flashSuccess('Beitrag erstellt.');
        return $this->redirect('/admin/beitraege');
    }

    public function edit(Request $request, string $id): Response
    {
        $post = Post::find((int) $id);
        if ($post === null) { return $this->redirect('/admin/beitraege'); }
        return $this->view('admin.posts.form', [
            'title' => 'Beitrag bearbeiten', 'post' => $post,
            'statuses' => PostStatus::cases(), 'currentRoute' => 'admin.posts',
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $post = Post::find((int) $id);
        if ($post === null) { return $this->redirect('/admin/beitraege'); }

        $post->setAttribute('title', strip_tags((string) $request->post('title', '')));
        $post->setAttribute('content', (string) $request->post('content', ''));
        $post->setAttribute('status', (string) $request->post('status', PostStatus::Draft->value));
        $post->setAttribute('meta_title', strip_tags((string) $request->post('meta_title', '')));
        $post->setAttribute('meta_description', strip_tags((string) $request->post('meta_description', '')));
        $post->save();

        $this->flashSuccess('Beitrag gespeichert.');
        return $this->redirect('/admin/beitraege');
    }

    public function delete(Request $request, string $id): Response
    {
        $p = $this->db->prefix();
        $this->db->statement("UPDATE `{$p}posts` SET deleted_at = NOW() WHERE id = ?", [(int) $id]);
        if ($request->wantsJson()) return $this->json(['success' => true]);
        $this->flashSuccess('Beitrag in Papierkorb verschoben.');
        return $this->redirect('/admin/beitraege');
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title) ?? $title);
        return trim($slug, '-') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }
}
