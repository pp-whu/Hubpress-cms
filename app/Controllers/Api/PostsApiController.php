<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Api;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};
use HuberCMS\Models\Post;
use HuberCMS\Enums\PostStatus;

/**
 * PostsApiController — REST Posts API
 * @package HuberCMS\Controllers\Api
 */
final class PostsApiController extends Controller
{
    public function __construct(Session $session) { parent::__construct($session); }

    public function index(Request $request): Response {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, (int) $request->query('per_page', 15));
        $posts = Post::query()->where('status', PostStatus::Published->value)->whereNull('deleted_at')->orderBy('published_at', 'DESC')->forPage($page, $perPage)->get();
        return $this->json(['data' => $posts, 'page' => $page, 'per_page' => $perPage]);
    }

    public function show(Request $request, string $id): Response {
        $post = Post::find((int) $id);
        return $post !== null ? $this->json($post->toArray()) : $this->json(['error' => 'Not found'], 404);
    }

    public function store(Request $request): Response {
        $data = $request->all();
        if (empty($data['title'])) return $this->json(['error' => 'title is required'], 422);
        $post = Post::create(['title' => strip_tags($data['title']), 'content' => $data['content'] ?? '', 'status' => PostStatus::Draft->value, 'author_id' => 1, 'slug' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']) ?? '')]);
        return $this->json($post->toArray(), 201);
    }

    public function update(Request $request, string $id): Response {
        $post = Post::find((int) $id);
        if ($post === null) return $this->json(['error' => 'Not found'], 404);
        foreach (['title', 'content', 'status'] as $f) { if (isset($request->all()[$f])) $post->setAttribute($f, $request->all()[$f]); }
        $post->save();
        return $this->json($post->toArray());
    }

    public function delete(Request $request, string $id): Response {
        $post = Post::find((int) $id);
        if ($post === null) return $this->json(['error' => 'Not found'], 404);
        $post->setAttribute('deleted_at', date('Y-m-d H:i:s')); $post->save();
        return Response::noContent();
    }
}
