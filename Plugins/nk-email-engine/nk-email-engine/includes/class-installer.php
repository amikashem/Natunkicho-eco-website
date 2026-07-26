<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Installer {

    const DB_VERSION_OPTION = 'nk_email_engine_db_version';
    const DB_VERSION        = '1.0.0';

    public static function activate() {
        self::create_tables();
        self::schedule_cron();
        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
    }

    public static function deactivate() {
        $timestamp = wp_next_scheduled( 'nk_process_email_queue' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'nk_process_email_queue' );
        }
        // Note: tables are intentionally NOT dropped on deactivation to protect data.
        // Provide a separate "uninstall.php" + admin confirmation if full removal is ever needed.
    }

    public static function maybe_upgrade() {
        if ( get_option( self::DB_VERSION_OPTION ) !== self::DB_VERSION ) {
            self::create_tables();
            update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
        }
    }

    private static function schedule_cron() {
        if ( ! wp_next_scheduled( 'nk_process_email_queue' ) ) {
            wp_schedule_event( time(), 'every_minute', 'nk_process_email_queue' );
        }
    }

    private static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $subscribers       = NK_Database::table( 'subscribers' );
        $email_queue       = NK_Database::table( 'email_queue' );
        $email_logs        = NK_Database::table( 'email_logs' );
        $email_templates   = NK_Database::table( 'email_templates' );
        $provider_settings = NK_Database::table( 'provider_settings' );
        $suppression_list  = NK_Database::table( 'suppression_list' );

        $sql = "
        CREATE TABLE {$subscribers} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NULL,
            status ENUM('active','pending','unsubscribed','bounced','complaint','suppressed') NOT NULL DEFAULT 'pending',
            source VARCHAR(100) NULL,
            interests TEXT NULL,
            verification_token VARCHAR(255) NULL,
            unsubscribe_token VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status)
        ) {$charset_collate};

        CREATE TABLE {$email_queue} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            recipient_email VARCHAR(255) NOT NULL,
            recipient_name VARCHAR(255) NULL,
            subject TEXT NOT NULL,
            body LONGTEXT NOT NULL,
            provider VARCHAR(50) NULL,
            priority VARCHAR(20) NOT NULL DEFAULT 'normal',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            retry_count INT NOT NULL DEFAULT 0,
            scheduled_at DATETIME NULL,
            sent_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY recipient_email (recipient_email),
            KEY status (status),
            KEY scheduled_at (scheduled_at),
            KEY provider (provider)
        ) {$charset_collate};

        CREATE TABLE {$email_logs} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email_queue_id BIGINT UNSIGNED NULL,
            recipient_email VARCHAR(255) NOT NULL,
            provider VARCHAR(50) NULL,
            status VARCHAR(50) NOT NULL,
            opened TINYINT(1) NOT NULL DEFAULT 0,
            clicked TINYINT(1) NOT NULL DEFAULT 0,
            bounced TINYINT(1) NOT NULL DEFAULT 0,
            complaint TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY email_queue_id (email_queue_id),
            KEY recipient_email (recipient_email)
        ) {$charset_collate};

        CREATE TABLE {$email_templates} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            template_name VARCHAR(255) NOT NULL,
            template_type VARCHAR(100) NULL,
            subject TEXT NOT NULL,
            html_content LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};

        CREATE TABLE {$provider_settings} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider_name VARCHAR(50) NOT NULL,
            api_key TEXT NULL,
            secret_key TEXT NULL,
            region VARCHAR(100) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY provider_name (provider_name)
        ) {$charset_collate};

        CREATE TABLE {$suppression_list} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            reason VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) {$charset_collate};
        ";

        // dbDelta needs each CREATE TABLE statement separated and fed individually.
        foreach ( array_filter( array_map( 'trim', explode( ';', $sql ) ) ) as $statement ) {
            dbDelta( $statement . ';' );
        }
    }
}
