<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Frontend;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Models\Post;

/**
 * HomeController
 *
 * Renders the public homepage with recent posts.
 *
 * @package HuberCMS\Controllers\Frontend
 */
final class HomeController extends Controller
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        // Catch DB errors gracefully — show empty homepage if DB not reachable
        try {
            $posts = Post::published();
            $posts = array_slice($posts, 0, 10);
        } catch (\Throwable) {
            $posts = [];
        }

        return $this->view('frontend.home', [
            'title' => $_ENV['APP_NAME'] ?? 'HuberCMS',
            'posts' => $posts,
        ]);
    }
}
