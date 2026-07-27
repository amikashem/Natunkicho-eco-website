<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateCompaniesTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('companies');
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (

            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

            user_id BIGINT UNSIGNED NOT NULL,

            company_name VARCHAR(255) NOT NULL,

            company_slug VARCHAR(255) NOT NULL,

            company_email VARCHAR(190) DEFAULT NULL,

            phone VARCHAR(50) DEFAULT NULL,

            website VARCHAR(255) DEFAULT NULL,

            logo VARCHAR(255) DEFAULT NULL,

            cover VARCHAR(255) DEFAULT NULL,

            industry VARCHAR(150) DEFAULT NULL,

            company_size VARCHAR(100) DEFAULT NULL,

            founded_year YEAR NULL,

            country VARCHAR(100) DEFAULT NULL,

            state VARCHAR(100) DEFAULT NULL,

            city VARCHAR(100) DEFAULT NULL,

            address TEXT NULL,

            description LONGTEXT NULL,

            verified TINYINT(1) DEFAULT 0,

            featured TINYINT(1) DEFAULT 0,

            status VARCHAR(20) DEFAULT 'active',

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (id),

            KEY user_id (user_id),
            KEY company_slug (company_slug),
            KEY country (country),
            KEY featured (featured),
            KEY status (status)

        ) {$charset};";

        dbDelta($sql);
    }
}