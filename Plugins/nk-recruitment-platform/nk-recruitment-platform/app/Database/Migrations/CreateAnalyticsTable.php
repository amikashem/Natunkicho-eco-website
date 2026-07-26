<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateAnalyticsTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('analytics_events');
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(50) NOT NULL,    -- e.g., 'job_view', 'job_apply', 'profile_view'
            entity_type VARCHAR(50) NOT NULL,   -- e.g., 'job', 'company', 'candidate'
            entity_id BIGINT UNSIGNED NOT NULL, -- The ID of the job or company
            user_id BIGINT UNSIGNED DEFAULT 0,  -- 0 if guest
            ip_hash VARCHAR(64) DEFAULT NULL,   -- Hashed IP for GDPR compliance/unique counts
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_entity (event_type, entity_type, entity_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}