<?php

declare(strict_types=1);

namespace HuberCMS\Database\Migrations;

use HuberCMS\Core\Database;

/**
 * CreateUsersTable Migration
 *
 * Creates the hcms_users table with all fields for
 * authentication, roles, 2FA and brute-force protection.
 *
 * @package HuberCMS\Database\Migrations
 */
final class CreateUsersTable
{
    public function __construct(private readonly Database $db)
    {
    }

    public function up(): void
    {
        $prefix = $this->db->prefix();

        $this->db->raw("
            CREATE TABLE IF NOT EXISTS `{$prefix}users` (
                `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `username`                VARCHAR(50)  NOT NULL UNIQUE,
                `email`                   VARCHAR(255) NOT NULL UNIQUE,
                `password`                VARCHAR(255) NOT NULL,
                `role`                    ENUM('super_admin','admin','editor','author','contributor','subscriber')
                                          NOT NULL DEFAULT 'subscriber',
                `first_name`              VARCHAR(100) NULL DEFAULT NULL,
                `last_name`               VARCHAR(100) NULL DEFAULT NULL,
                `avatar`                  VARCHAR(500) NULL DEFAULT NULL,
                `bio`                     TEXT         NULL DEFAULT NULL,
                `is_active`               TINYINT(1)   NOT NULL DEFAULT 1,
                `two_factor_enabled`      TINYINT(1)   NOT NULL DEFAULT 0,
                `two_factor_secret`       VARCHAR(255) NULL DEFAULT NULL,
                `login_attempts`          TINYINT      NOT NULL DEFAULT 0,
                `locked_until`            DATETIME     NULL DEFAULT NULL,
                `remember_token`          VARCHAR(100) NULL DEFAULT NULL,
                `password_reset_token`    VARCHAR(255) NULL DEFAULT NULL,
                `password_reset_expires`  DATETIME     NULL DEFAULT NULL,
                `last_login_at`           DATETIME     NULL DEFAULT NULL,
                `last_login_ip`           VARCHAR(45)  NULL DEFAULT NULL,
                `created_at`              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_email`         (`email`),
                INDEX `idx_username`      (`username`),
                INDEX `idx_role`          (`role`),
                INDEX `idx_reset_token`   (`password_reset_token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $prefix = $this->db->prefix();
        $this->db->raw("DROP TABLE IF EXISTS `{$prefix}users`;");
    }
}
