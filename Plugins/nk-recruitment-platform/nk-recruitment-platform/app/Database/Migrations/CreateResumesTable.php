<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateResumesTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('resumes');
        $charset = $wpdb->get_charset_collate();

        // Strict dbDelta Formatting
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            candidate_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            resume_title VARCHAR(255) NOT NULL,
            objective TEXT,
            education_data LONGTEXT,
            experience_data LONGTEXT,
            skills_data LONGTEXT,
            certifications_data LONGTEXT,
            languages_data LONGTEXT,
            portfolio_data LONGTEXT,
            ai_parsed_data LONGTEXT,
            file_path VARCHAR(500) DEFAULT NULL,
            file_type VARCHAR(50) DEFAULT 'manual',
            is_primary TINYINT(1) DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY candidate_id (candidate_id),
            KEY user_id (user_id),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}