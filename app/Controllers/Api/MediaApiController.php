<?php declare(strict_types=1);
namespace HuberCMS\Controllers\Api;
use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};
use HuberCMS\Models\Media;
use HuberCMS\Services\MediaService;

/** MediaApiController @package HuberCMS\Controllers\Api */
final class MediaApiController extends Controller
{
    public function __construct(Session $session, private readonly MediaService $mediaService) { parent::__construct($session); }

    public function index(Request $request): Response {
        return $this->json(array_map(fn($m) => $m->toArray(), Media::all()));
    }

    public function upload(Request $request): Response {
        $file = $request->file('file');
        if ($file === null) return $this->json(['error' => 'No file uploaded'], 400);
        try {
            $m = $this->mediaService->upload($file, 1);
            return $this->json($m->toArray(), 201);
        } catch (\Throwable) {
            return $this->json(['error' => 'Datei konnte nicht verarbeitet werden.'], 422);
        }
    }

    public function delete(Request $request, string $id): Response {
        $this->mediaService->delete((int) $id);
        return Response::noContent();
    }
}
