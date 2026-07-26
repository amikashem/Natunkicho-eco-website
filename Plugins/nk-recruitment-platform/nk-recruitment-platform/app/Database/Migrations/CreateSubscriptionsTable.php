<?php

declare(strict_types=1);

namespace NKRecruitment\Database\Migrations;

use NKRecruitment\Database\Migration;
use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class CreateSubscriptionsTable extends Migration
{
    public function up(): void
    {
        global $wpdb;

        $table = DatabaseManager::table('subscriptions');
        $charset = $wpdb->get_charset_collate();

        // user_id links directly to the WordPress wp_users table
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            user_type VARCHAR(20) NOT NULL DEFAULT 'employer', 
            plan_key VARCHAR(50) NOT NULL DEFAULT 'free',
            jobs_posted INT UNSIGNED DEFAULT 0,
            applications_viewed INT UNSIGNED DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}