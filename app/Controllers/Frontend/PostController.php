<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Frontend;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Models\Post;

/**
 * PostController — Frontend blog
 *
 * @package HuberCMS\Controllers\Frontend
 */
final class PostController extends Controller
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        try {
            $posts = Post::published();
        } catch (\Throwable) {
            $posts = [];
        }
        return $this->view('frontend.blog.index', ['title' => 'Blog', 'posts' => $posts]);
    }

    public function show(Request $request, string $slug): Response
    {
        try {
            $post = Post::findBySlug($slug);
        } catch (\Throwable) {
            $post = null;
        }

        if ($post === null) {
            return Response::html('<h1>404 — Beitrag nicht gefunden</h1>', 404);
        }

        return $this->view('frontend.blog.show', ['title' => $post->getAttribute('title'), 'post' => $post]);
    }
}
