<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateJobsTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('jobs');

        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table}(

            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

            company_id BIGINT UNSIGNED NOT NULL,

            user_id BIGINT UNSIGNED NOT NULL,

            job_title VARCHAR(255) NOT NULL,

            job_slug VARCHAR(255) NOT NULL,

            job_code VARCHAR(100) DEFAULT NULL,

            department VARCHAR(150) DEFAULT NULL,

            category VARCHAR(150) DEFAULT NULL,

            employment_type VARCHAR(100) DEFAULT NULL,

            workplace_type VARCHAR(100) DEFAULT NULL,

            experience_level VARCHAR(100) DEFAULT NULL,

            vacancies INT DEFAULT 1,

            salary_min DECIMAL(12,2) DEFAULT NULL,

            salary_max DECIMAL(12,2) DEFAULT NULL,

            salary_currency VARCHAR(10) DEFAULT 'USD',

            salary_period VARCHAR(30) DEFAULT 'month',

            country VARCHAR(100) DEFAULT NULL,

            state VARCHAR(100) DEFAULT NULL,

            city VARCHAR(100) DEFAULT NULL,

            address TEXT NULL,

            education VARCHAR(150) DEFAULT NULL,

            gender VARCHAR(50) DEFAULT NULL,

            visa_sponsorship TINYINT(1) DEFAULT 0,

            accommodation TINYINT(1) DEFAULT 0,

            transport TINYINT(1) DEFAULT 0,

            meal TINYINT(1) DEFAULT 0,

            contract_length VARCHAR(100) DEFAULT NULL,

            description LONGTEXT NULL,

            requirements LONGTEXT NULL,

            responsibilities LONGTEXT NULL,

            benefits LONGTEXT NULL,

            deadline DATE DEFAULT NULL,
            
            external_apply_url VARCHAR(255) DEFAULT NULL,

            featured TINYINT(1) DEFAULT 0,

            status VARCHAR(20) DEFAULT 'draft',

            views BIGINT DEFAULT 0,

            applications BIGINT DEFAULT 0,

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY(id),

            KEY company_id(company_id),

            KEY user_id(user_id),

            KEY job_slug(job_slug),

            KEY department(department),

            KEY category(category),

            KEY employment_type(employment_type),

            KEY workplace_type(workplace_type),

            KEY status(status),

            KEY featured(featured),

            KEY country(country),

            KEY city(city),

            KEY deadline(deadline)

        ) {$charset};";

        dbDelta($sql);
    }
}