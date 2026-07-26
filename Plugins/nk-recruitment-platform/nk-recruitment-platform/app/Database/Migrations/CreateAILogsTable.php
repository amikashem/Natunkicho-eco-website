<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateAILogsTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('ai_logs');
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED DEFAULT 0,
            module VARCHAR(50) NOT NULL,       -- e.g., 'jobs', 'resume', 'ats'
            action VARCHAR(50) NOT NULL,       -- e.g., 'generate_jd', 'optimize_cv'
            provider VARCHAR(50) NOT NULL,     -- e.g., 'openai', 'gemini'
            model_used VARCHAR(50) NOT NULL,   -- e.g., 'gpt-4o-mini'
            prompt_tokens INT UNSIGNED DEFAULT 0,
            completion_tokens INT UNSIGNED DEFAULT 0,
            total_tokens INT UNSIGNED DEFAULT 0,
            estimated_cost DECIMAL(10, 6) DEFAULT 0.000000, -- Micro-cent accuracy
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY module (module),
            KEY user_id (user_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}