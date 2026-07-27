<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

class CreateCandidateIndexTable
{
    public function up(): void
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'nkrp_candidate_index';
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$tableName} (
            user_id bigint(20) UNSIGNED NOT NULL,
            display_name varchar(255) NOT NULL,
            professional_title varchar(255) DEFAULT NULL,
            skills text DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            bio text DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (user_id),
            FULLTEXT KEY search_index (display_name, professional_title, skills, bio)
        ) {$charsetCollate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // --- 🛡️ ERROR ISOLATION APPLIED ---
        try {
            dbDelta($sql);
        } catch (\Throwable $e) {
            error_log('NKRP DB Migration Error (Candidate Index): ' . $e->getMessage());
        }
    }
}