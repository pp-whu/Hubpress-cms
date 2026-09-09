<?php

declare(strict_types=1);

namespace HuberCMS\Database\Migrations;

use HuberCMS\Core\Database;

/**
 * CreateCmsCoreTables Migration
 *
 * Creates: posts, pages, categories, tags, post_categories, post_tags,
 * media, comments, menus, menu_items, settings, migrations log.
 *
 * @package HuberCMS\Database\Migrations
 */
final class CreateCmsCoreTables
{
    public function __construct(private readonly Database $db)
    {
    }

    public function up(): void
    {
        $p = $this->db->prefix();

        // Migrations log
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}migrations` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `migration`  VARCHAR(255) NOT NULL UNIQUE,
                `batch`      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
                `ran_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Settings / key-value store
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}settings` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `group`        VARCHAR(100) NOT NULL DEFAULT 'general',
                `key`          VARCHAR(255) NOT NULL,
                `value`        LONGTEXT     NULL,
                `type`         VARCHAR(30)  NOT NULL DEFAULT 'string',
                `autoload`     TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_group_key` (`group`, `key`),
                INDEX `idx_autoload` (`autoload`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Categories
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}categories` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
                `name`        VARCHAR(255) NOT NULL,
                `slug`        VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT         NULL,
                `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
                `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Tags
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}tags` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`       VARCHAR(100) NOT NULL,
                `slug`       VARCHAR(100) NOT NULL UNIQUE,
                `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Media
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}media` (
                `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`          INT UNSIGNED NOT NULL,
                `filename`         VARCHAR(255) NOT NULL,
                `disk_path`        VARCHAR(500) NOT NULL,
                `public_url`       VARCHAR(500) NOT NULL,
                `mime_type`        VARCHAR(100) NOT NULL,
                `size`             INT UNSIGNED NOT NULL DEFAULT 0,
                `type`             ENUM('image','video','audio','document','archive','other') NOT NULL DEFAULT 'other',
                `width`            SMALLINT UNSIGNED NULL DEFAULT NULL,
                `height`           SMALLINT UNSIGNED NULL DEFAULT NULL,
                `alt_text`         VARCHAR(500) NULL DEFAULT NULL,
                `caption`          TEXT         NULL DEFAULT NULL,
                `thumbnail_path`   VARCHAR(500) NULL DEFAULT NULL,
                `webp_path`        VARCHAR(500) NULL DEFAULT NULL,
                `avif_path`        VARCHAR(500) NULL DEFAULT NULL,
                `folder`           VARCHAR(255) NULL DEFAULT NULL,
                `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_user`   (`user_id`),
                INDEX `idx_type`   (`type`),
                INDEX `idx_folder` (`folder`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Posts
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}posts` (
                `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `author_id`         INT UNSIGNED NOT NULL,
                `featured_image_id` INT UNSIGNED NULL DEFAULT NULL,
                `title`             VARCHAR(500) NOT NULL,
                `slug`              VARCHAR(500) NOT NULL,
                `content`           LONGTEXT     NOT NULL DEFAULT '',
                `excerpt`           TEXT         NULL DEFAULT NULL,
                `status`            ENUM('draft','published','scheduled','trashed') NOT NULL DEFAULT 'draft',
                `meta_title`        VARCHAR(500) NULL DEFAULT NULL,
                `meta_description`  VARCHAR(1000) NULL DEFAULT NULL,
                `og_image`          VARCHAR(500) NULL DEFAULT NULL,
                `allow_comments`    TINYINT(1)   NOT NULL DEFAULT 1,
                `likes`             INT UNSIGNED NOT NULL DEFAULT 0,
                `views`             INT UNSIGNED NOT NULL DEFAULT 0,
                `comment_count`     INT UNSIGNED NOT NULL DEFAULT 0,
                `version`           SMALLINT     NOT NULL DEFAULT 1,
                `published_at`      DATETIME     NULL DEFAULT NULL,
                `scheduled_at`      DATETIME     NULL DEFAULT NULL,
                `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`        DATETIME     NULL DEFAULT NULL,
                UNIQUE KEY `uq_slug` (`slug`),
                INDEX `idx_status`  (`status`),
                INDEX `idx_author`  (`author_id`),
                INDEX `idx_published` (`published_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Pages
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}pages` (
                `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `parent_id`        INT UNSIGNED NULL DEFAULT NULL,
                `author_id`        INT UNSIGNED NOT NULL,
                `title`            VARCHAR(500) NOT NULL,
                `slug`             VARCHAR(500) NOT NULL,
                `content`          LONGTEXT     NOT NULL DEFAULT '',
                `status`           ENUM('draft','published','scheduled','trashed') NOT NULL DEFAULT 'draft',
                `template`         VARCHAR(100) NOT NULL DEFAULT 'default',
                `sort_order`       SMALLINT     NOT NULL DEFAULT 0,
                `show_in_menu`     TINYINT(1)   NOT NULL DEFAULT 0,
                `meta_title`       VARCHAR(500) NULL DEFAULT NULL,
                `meta_description` VARCHAR(1000) NULL DEFAULT NULL,
                `version`          SMALLINT     NOT NULL DEFAULT 1,
                `published_at`     DATETIME     NULL DEFAULT NULL,
                `scheduled_at`     DATETIME     NULL DEFAULT NULL,
                `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`       DATETIME     NULL DEFAULT NULL,
                UNIQUE KEY `uq_slug` (`slug`),
                INDEX `idx_parent` (`parent_id`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Comments
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}comments` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `post_id`     INT UNSIGNED NOT NULL,
                `user_id`     INT UNSIGNED NULL DEFAULT NULL,
                `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
                `author_name` VARCHAR(100) NULL DEFAULT NULL,
                `author_email` VARCHAR(255) NULL DEFAULT NULL,
                `content`     TEXT         NOT NULL,
                `status`      ENUM('pending','approved','spam','trashed') NOT NULL DEFAULT 'pending',
                `ip_address`  VARCHAR(45)  NULL DEFAULT NULL,
                `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_post`   (`post_id`),
                INDEX `idx_status` (`status`),
                INDEX `idx_parent` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Post Categories (pivot)
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}post_categories` (
                `post_id`     INT UNSIGNED NOT NULL,
                `category_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`post_id`, `category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Post Tags (pivot)
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}post_tags` (
                `post_id` INT UNSIGNED NOT NULL,
                `tag_id`  INT UNSIGNED NOT NULL,
                PRIMARY KEY (`post_id`, `tag_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Menus
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}menus` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`       VARCHAR(100) NOT NULL,
                `slug`       VARCHAR(100) NOT NULL UNIQUE,
                `location`   VARCHAR(100) NULL DEFAULT NULL,
                `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Menu Items
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}menu_items` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `menu_id`     INT UNSIGNED NOT NULL,
                `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
                `label`       VARCHAR(255) NOT NULL,
                `url`         VARCHAR(500) NOT NULL,
                `target`      VARCHAR(20)  NOT NULL DEFAULT '_self',
                `icon`        VARCHAR(100) NULL DEFAULT NULL,
                `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
                INDEX `idx_menu`   (`menu_id`),
                INDEX `idx_parent` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Audit Log
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}audit_logs` (
                `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`    INT UNSIGNED    NULL DEFAULT NULL,
                `action`     VARCHAR(100)    NOT NULL,
                `model`      VARCHAR(100)    NULL DEFAULT NULL,
                `model_id`   INT UNSIGNED    NULL DEFAULT NULL,
                `old_values` JSON            NULL DEFAULT NULL,
                `new_values` JSON            NULL DEFAULT NULL,
                `ip_address` VARCHAR(45)     NULL DEFAULT NULL,
                `user_agent` VARCHAR(500)    NULL DEFAULT NULL,
                `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user`   (`user_id`),
                INDEX `idx_action` (`action`),
                INDEX `idx_model`  (`model`, `model_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // API Keys
        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$p}api_keys` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`      INT UNSIGNED  NOT NULL,
                `name`         VARCHAR(100)  NOT NULL,
                `key_hash`     VARCHAR(255)  NOT NULL UNIQUE,
                `permissions`  JSON          NULL DEFAULT NULL,
                `last_used_at` DATETIME      NULL DEFAULT NULL,
                `expires_at`   DATETIME      NULL DEFAULT NULL,
                `is_active`    TINYINT(1)    NOT NULL DEFAULT 1,
                `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user`     (`user_id`),
                INDEX `idx_key_hash` (`key_hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $p = $this->db->prefix();
        $tables = [
            'api_keys', 'audit_logs', 'menu_items', 'menus',
            'post_tags', 'post_categories', 'comments',
            'pages', 'posts', 'media', 'tags', 'categories', 'settings', 'migrations'
        ];
        foreach ($tables as $table) {
            $this->db->raw("DROP TABLE IF EXISTS `{$p}{$table}`;");
        }
    }
}
