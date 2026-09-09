<?php

declare(strict_types=1);

namespace HuberCMS\Enums;

/**
 * MediaType
 *
 * Categorizes uploaded files by type.
 *
 * @package HuberCMS\Enums
 */
enum MediaType: string
{
    case Image    = 'image';
    case Video    = 'video';
    case Audio    = 'audio';
    case Document = 'document';
    case Archive  = 'archive';
    case Other    = 'other';

    /**
     * Resolves the media type from a MIME type string.
     */
    public static function fromMime(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/')       => self::Image,
            str_starts_with($mimeType, 'video/')       => self::Video,
            str_starts_with($mimeType, 'audio/')       => self::Audio,
            in_array($mimeType, [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain', 'text/csv',
            ], true)                                   => self::Document,
            in_array($mimeType, [
                'application/zip',
                'application/x-tar',
                'application/x-gzip',
                'application/x-7z-compressed',
            ], true)                                   => self::Archive,
            default                                    => self::Other,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Image    => 'Bild',
            self::Video    => 'Video',
            self::Audio    => 'Audio',
            self::Document => 'Dokument',
            self::Archive  => 'Archiv',
            self::Other    => 'Sonstige',
        };
    }
}
