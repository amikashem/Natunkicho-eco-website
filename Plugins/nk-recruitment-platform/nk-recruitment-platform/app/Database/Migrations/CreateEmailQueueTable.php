<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateEmailQueueTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('email_queue');
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            recipient_email VARCHAR(150) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body LONGTEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending', 
            attempts TINYINT UNSIGNED DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            sent_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}