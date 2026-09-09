<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Frontend;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Models\Page;

/**
 * PageController — Frontend static pages
 *
 * @package HuberCMS\Controllers\Frontend
 */
final class PageController extends Controller
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function show(Request $request, string $slug): Response
    {
        $page = Page::findBySlug($slug);

        if ($page === null) {
            return Response::html('<h1>404 — Seite nicht gefunden</h1>', 404);
        }

        return $this->view('frontend.page', ['title' => $page->title, 'page' => $page]);
    }
}
