<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

class CreateMessagesTable
{
    public function up(): void
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . 'nkrp_messages';
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            sender_id bigint(20) UNSIGNED NOT NULL,
            receiver_id bigint(20) UNSIGNED NOT NULL,
            job_id bigint(20) UNSIGNED DEFAULT NULL,
            message text NOT NULL,
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY sender_id (sender_id),
            KEY receiver_id (receiver_id),
            KEY is_read (is_read)
        ) {$charsetCollate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}