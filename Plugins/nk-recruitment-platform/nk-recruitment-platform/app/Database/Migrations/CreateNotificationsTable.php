<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateNotificationsTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('notifications');
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'info', /* info, success, warning */
            is_read TINYINT(1) DEFAULT 0,
            action_url VARCHAR(500) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_read (is_read)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}