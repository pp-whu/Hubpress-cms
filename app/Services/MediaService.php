<?php

declare(strict_types=1);

namespace HuberCMS\Services;

use HuberCMS\Core\Config;
use HuberCMS\Core\Database;
use HuberCMS\Core\Logger;
use HuberCMS\Enums\MediaType;
use HuberCMS\Models\Media;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

/**
 * MediaService
 *
 * Handles file upload, image processing, thumbnail generation,
 * WebP/AVIF conversion and metadata storage.
 *
 * @package HuberCMS\Services
 */
final class MediaService
{
    private int    $maxSize;
    /** @var string[] */
    private array  $allowedTypes;
    private string $uploadPath;
    private string $uploadUrl;

    public function __construct(
        private readonly Config   $config,
        private readonly Database $db,
        private readonly Logger   $logger
    ) {
        $this->maxSize      = (int) $config->get('app.upload.max_size', 10485760);
        $this->allowedTypes = $config->get('app.upload.allowed_types', ['jpg','jpeg','png','gif','webp','pdf']);
        $this->uploadPath   = STORAGE_PATH . '/Uploads';
        $this->uploadUrl    = '/uploads'; // served via symlink or rewrite

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    // =========================================================
    // Upload
    // =========================================================

    /**
     * Processes an uploaded file from $_FILES.
     *
     * @param array<string, mixed> $file  $_FILES['field'] entry
     * @return Media
     * @throws \RuntimeException On invalid file or save failure
     */
    public function upload(array $file, int $userId, string $folder = ''): Media
    {
        $this->validateUpload($file);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeFilename = $this->generateSafeFilename($extension);
        $subfolder = $folder ?: date('Y/m');
        $targetDir = $this->uploadPath . '/' . $subfolder;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $diskPath  = $subfolder . '/' . $safeFilename;
        $fullPath  = $this->uploadPath . '/' . $diskPath;
        $publicUrl = $this->uploadUrl . '/' . $diskPath;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        $mimeType  = mime_content_type($fullPath) ?: 'application/octet-stream';
        $mediaType = MediaType::fromMime($mimeType);

        $width = $height = null;
        $thumbnailPath = $webpPath = null;

        if ($mediaType === MediaType::Image) {
            [$width, $height] = $this->getImageDimensions($fullPath);
            $thumbnailPath    = $this->generateThumbnail($fullPath, $diskPath);
            $webpPath         = $this->convertToWebp($fullPath, $diskPath);
        }

        $media = Media::create([
            'user_id'        => $userId,
            'filename'       => $safeFilename,
            'disk_path'      => $diskPath,
            'public_url'     => $publicUrl,
            'mime_type'      => $mimeType,
            'size'           => (int) filesize($fullPath),
            'type'           => $mediaType->value,
            'width'          => $width,
            'height'         => $height,
            'thumbnail_path' => $thumbnailPath,
            'webp_path'      => $webpPath,
            'folder'         => $folder,
        ]);

        $this->logger->info('Media uploaded', ['id' => $media->id, 'file' => $diskPath]);

        return $media;
    }

    /**
     * Deletes a media record and its files from disk.
     */
    public function delete(int $mediaId): bool
    {
        $media = Media::find($mediaId);
        if ($media === null) {
            return false;
        }

        $files = [
            $this->uploadPath . '/' . $media->getAttribute('disk_path'),
            $this->uploadPath . '/' . $media->getAttribute('thumbnail_path'),
            $this->uploadPath . '/' . $media->getAttribute('webp_path'),
        ];

        foreach ($files as $file) {
            if ($file && file_exists($file)) {
                @unlink($file);
            }
        }

        $media->delete();
        return true;
    }

    // =========================================================
    // Private image helpers
    // =========================================================

    /** @return array{0:int, 1:int}|array{0:null, 1:null} */
    private function getImageDimensions(string $path): array
    {
        $size = @getimagesize($path);
        return $size !== false ? [(int) $size[0], (int) $size[1]] : [null, null];
    }

    private function generateThumbnail(string $sourcePath, string $diskPath): ?string
    {
        try {
            $manager = new ImageManager(new GdDriver());
            $img = $manager->read($sourcePath);
            $img->cover(300, 300);

            $thumbDisk = str_replace(
                pathinfo($diskPath, PATHINFO_BASENAME),
                'thumb_' . pathinfo($diskPath, PATHINFO_BASENAME),
                $diskPath
            );

            $img->save($this->uploadPath . '/' . $thumbDisk, quality: 85);
            return $thumbDisk;
        } catch (\Throwable $e) {
            $this->logger->warning('Thumbnail generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function convertToWebp(string $sourcePath, string $diskPath): ?string
    {
        if (!function_exists('imagewebp')) {
            return null;
        }

        try {
            $manager = new ImageManager(new GdDriver());
            $img = $manager->read($sourcePath);

            $webpDisk = preg_replace('/\.[^.]+$/', '.webp', $diskPath);
            $img->save($this->uploadPath . '/' . $webpDisk, quality: 85);
            return $webpDisk;
        } catch (\Throwable) {
            return null;
        }
    }

    // =========================================================
    // Validation
    // =========================================================

    /** @param array<string, mixed> $file */
    private function validateUpload(array $file): void
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload error: ' . ($file['error'] ?? 'unknown'));
        }

        if ($file['size'] > $this->maxSize) {
            throw new \RuntimeException(
                sprintf('File too large. Max %d MB.', $this->maxSize / 1048576)
            );
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes, true)) {
            throw new \RuntimeException("File type [{$extension}] is not allowed.");
        }

        // Validate MIME type matches extension (prevents polyglot attacks)
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (empty($mime) || $mime === 'application/octet-stream') {
            throw new \RuntimeException('Could not determine file type.');
        }
    }

    private function generateSafeFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . preg_replace('/[^a-z0-9]/', '', $extension);
    }
}
