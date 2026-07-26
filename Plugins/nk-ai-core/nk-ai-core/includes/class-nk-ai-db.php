<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_AI_DB {

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // 1. Providers Table (Stores API Keys securely)
        $sql_providers = "CREATE TABLE {$wpdb->prefix}nk_ai_providers (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            provider_name varchar(100) NOT NULL,
            api_key_encrypted text NOT NULL,
            is_active boolean DEFAULT 1,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_providers );

        // 2. Prompts Library Table (Version controlled prompts)
        $sql_prompts = "CREATE TABLE {$wpdb->prefix}nk_ai_prompts (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            module_name varchar(100) NOT NULL,
            prompt_key varchar(100) NOT NULL,
            system_prompt text NOT NULL,
            version varchar(20) DEFAULT '1.0',
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_prompts );

        // 3. Cache Table (Never pay for the same API response twice)
        $sql_cache = "CREATE TABLE {$wpdb->prefix}nk_ai_cache (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            request_hash varchar(64) NOT NULL,
            provider varchar(50) NOT NULL,
            response_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY request_hash (request_hash)
        ) $charset_collate;";
        dbDelta( $sql_cache );

        // 4. Logs & Usage Table (Cost tracking)
        $sql_logs = "CREATE TABLE {$wpdb->prefix}nk_ai_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT 0,
            module_name varchar(100) NOT NULL,
            provider varchar(50) NOT NULL,
            tokens_used int(11) DEFAULT 0,
            estimated_cost decimal(10,6) DEFAULT 0.000000,
            response_time_ms int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_logs );

        // 5. Queue Table (For heavy background jobs)
        $sql_queue = "CREATE TABLE {$wpdb->prefix}nk_ai_queue (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            job_type varchar(100) NOT NULL,
            payload longtext NOT NULL,
            status varchar(20) DEFAULT 'pending',
            attempts int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_queue );
    }
}