<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateCandidatesTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('candidates');
        $charset = $wpdb->get_charset_collate();

        // Strict dbDelta Formatting:
        // 1. Two spaces after PRIMARY KEY
        // 2. Space before parenthesis on KEY
        // 3. No explicit NULL on TEXT fields
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            professional_title VARCHAR(255) DEFAULT NULL,
            location_city VARCHAR(100) DEFAULT NULL,
            location_country VARCHAR(100) DEFAULT NULL,
            date_of_birth DATE DEFAULT NULL,
            gender VARCHAR(50) DEFAULT NULL,
            nationality VARCHAR(100) DEFAULT NULL,
            current_salary DECIMAL(12,2) DEFAULT NULL,
            expected_salary DECIMAL(12,2) DEFAULT NULL,
            salary_currency VARCHAR(10) DEFAULT 'USD',
            experience_years INT DEFAULT 0,
            education_level VARCHAR(100) DEFAULT NULL,
            availability VARCHAR(100) DEFAULT NULL,
            bio LONGTEXT,
            skills TEXT,
            languages TEXT,
            linkedin_url VARCHAR(255) DEFAULT NULL,
            portfolio_url VARCHAR(255) DEFAULT NULL,
            profile_photo_id BIGINT UNSIGNED DEFAULT NULL,
            resume_file_id BIGINT UNSIGNED DEFAULT NULL,
            is_featured TINYINT(1) DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            profile_views BIGINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY email (email),
            KEY professional_title (professional_title),
            KEY location_city (location_city),
            KEY location_country (location_country),
            KEY experience_years (experience_years),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}