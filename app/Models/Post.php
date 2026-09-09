<?php

declare(strict_types=1);

namespace HuberCMS\Models;

use HuberCMS\Core\Model;
use HuberCMS\Enums\PostStatus;
use HuberCMS\Traits\HasTimestamps;
use HuberCMS\Traits\HasSoftDelete;

/**
 * Post Model
 *
 * Blog posts with versioning, scheduling, categories and tags support.
 *
 * @package HuberCMS\Models
 *
 * @property int    $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $excerpt
 * @property string $status     (draft|published|scheduled|trashed)
 * @property int    $author_id
 * @property int|null $featured_image_id
 * @property string|null $published_at
 * @property string|null $scheduled_at
 * @property string $meta_title
 * @property string $meta_description
 * @property string|null $og_image
 * @property int    $likes
 * @property int    $views
 * @property int    $comment_count
 * @property bool   $allow_comments
 * @property int    $version
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
final class Post extends Model
{
    protected static string $table = 'posts';

    protected static array $fillable = [
        'title', 'slug', 'content', 'excerpt', 'status',
        'author_id', 'featured_image_id', 'published_at', 'scheduled_at',
        'meta_title', 'meta_description', 'og_image',
        'allow_comments',
    ];

    protected static array $casts = [
        'id'              => 'int',
        'author_id'       => 'int',
        'featured_image_id' => 'int',
        'likes'           => 'int',
        'views'           => 'int',
        'comment_count'   => 'int',
        'allow_comments'  => 'bool',
        'version'         => 'int',
    ];

    // =========================================================
    // Scoped queries
    // =========================================================

    /** @return static[] */
    public static function published(): array
    {
        $rows = static::query()
            ->where('status', PostStatus::Published->value)
            ->whereNull('deleted_at')
            ->orderBy('published_at', 'DESC')
            ->get();
        return array_map(fn($r) => static::hydrate($r), $rows);
    }

    /** @return static[] */
    public static function drafts(): array
    {
        $rows = static::query()
            ->where('status', PostStatus::Draft->value)
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'DESC')
            ->get();
        return array_map(fn($r) => static::hydrate($r), $rows);
    }

    public static function findBySlug(string $slug): ?self
    {
        $row = static::query()
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();
        return $row !== null ? static::hydrate($row) : null;
    }

    /**
     * Returns whether the post is publicly visible.
     */
    public function isPublished(): bool
    {
        return $this->getAttribute('status') === PostStatus::Published->value
            && $this->getAttribute('deleted_at') === null;
    }

    /**
     * Returns an auto-generated excerpt from content if not set.
     */
    public function getExcerpt(int $length = 160): string
    {
        $excerpt = $this->getAttribute('excerpt');
        if (!empty($excerpt)) {
            return $excerpt;
        }

        $plain = strip_tags((string) $this->getAttribute('content'));
        return mb_strlen($plain) > $length
            ? mb_substr($plain, 0, $length) . '…'
            : $plain;
    }
}
