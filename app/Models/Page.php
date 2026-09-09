<?php

declare(strict_types=1);

namespace HuberCMS\Models;

use HuberCMS\Core\Model;

/**
 * Page Model
 *
 * CMS pages (static content) with versioning, hierarchy (parent/child)
 * and scheduled publishing.
 *
 * @package HuberCMS\Models
 *
 * @property int         $id
 * @property int|null    $parent_id
 * @property string      $title
 * @property string      $slug
 * @property string      $content
 * @property string      $status
 * @property string      $template
 * @property int         $sort_order
 * @property bool        $show_in_menu
 * @property string|null $published_at
 * @property string|null $scheduled_at
 * @property string      $meta_title
 * @property string      $meta_description
 * @property int         $version
 * @property string      $created_at
 * @property string      $updated_at
 * @property string|null $deleted_at
 */
final class Page extends Model
{
    protected static string $table = 'pages';

    protected static array $fillable = [
        'parent_id', 'author_id', 'title', 'slug', 'content', 'status',
        'template', 'sort_order', 'show_in_menu',
        'published_at', 'scheduled_at',
        'meta_title', 'meta_description',
    ];

    protected static array $casts = [
        'id'           => 'int',
        'parent_id'    => 'int',
        'sort_order'   => 'int',
        'show_in_menu' => 'bool',
        'version'      => 'int',
    ];

    // =========================================================
    // Queries
    // =========================================================

    public static function findBySlug(string $slug): ?self
    {
        $row = static::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->first();
        return $row !== null ? static::hydrate($row) : null;
    }

    /** @return static[] */
    public static function topLevel(): array
    {
        $rows = static::query()
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get();
        return array_map(fn($r) => static::hydrate($r), $rows);
    }

    /** @return static[] Children of a given parent page */
    public static function childrenOf(int $parentId): array
    {
        $rows = static::query()
            ->where('parent_id', $parentId)
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get();
        return array_map(fn($r) => static::hydrate($r), $rows);
    }
}
