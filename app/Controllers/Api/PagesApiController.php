<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Api;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};
use HuberCMS\Models\Page;

/** PagesApiController @package HuberCMS\Controllers\Api */
final class PagesApiController extends Controller
{
    public function __construct(Session $session) { parent::__construct($session); }

    public function index(Request $request): Response {
        return $this->json(array_map(fn($p) => $p->toArray(), Page::all()));
    }

    public function show(Request $request, string $id): Response {
        $page = Page::find((int) $id);
        return $page !== null ? $this->json($page->toArray()) : $this->json(['error' => 'Not found'], 404);
    }
}
