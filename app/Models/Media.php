<?php

declare(strict_types=1);

namespace HuberCMS\Models;

use HuberCMS\Core\Model;

/**
 * Media Model
 *
 * Uploaded files: images, PDFs, videos, audio.
 * Stores original + generated thumbnail paths.
 *
 * @package HuberCMS\Models
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $filename        Original uploaded filename (sanitized)
 * @property string $disk_path       Relative path on disk
 * @property string $public_url      Public-facing URL
 * @property string $mime_type
 * @property int    $size            Bytes
 * @property string $type            image|video|audio|document|archive
 * @property int|null $width
 * @property int|null $height
 * @property string|null $alt_text
 * @property string|null $caption
 * @property string|null $thumbnail_path
 * @property string|null $webp_path
 * @property string|null $folder
 * @property string $created_at
 * @property string $updated_at
 */
final class Media extends Model
{
    protected static string $table = 'media';

    protected static array $fillable = [
        'user_id', 'filename', 'disk_path', 'public_url',
        'mime_type', 'size', 'type', 'width', 'height',
        'alt_text', 'caption', 'thumbnail_path', 'webp_path', 'folder',
    ];

    protected static array $casts = [
        'id'      => 'int',
        'user_id' => 'int',
        'size'    => 'int',
        'width'   => 'int',
        'height'  => 'int',
    ];

    /**
     * Returns the human-readable file size.
     */
    public function humanSize(): string
    {
        $bytes = (int) $this->getAttribute('size');
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * Checks whether the media item is an image.
     */
    public function isImage(): bool
    {
        return $this->getAttribute('type') === 'image';
    }
}
