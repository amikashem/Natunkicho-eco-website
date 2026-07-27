<?php
if (!defined('ABSPATH')) exit;

/**
 * NatunKicho AI CV Studio - Database Setup
 * Creates custom tables for scalable JSON CV storage.
 */
function nk_create_cv_builder_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table names
    $table_profiles = $wpdb->prefix . 'nk_cv_profiles';
    $table_sections = $wpdb->prefix . 'nk_cv_sections';

    // 1. Profiles Table
    $sql_profiles = "CREATE TABLE $table_profiles (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        profile_name varchar(255) NOT NULL,
        template_id varchar(50) DEFAULT 'modern',
        visibility varchar(20) DEFAULT 'private',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 2. Sections Table (JSON Data)
    $sql_sections = "CREATE TABLE $table_sections (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        profile_id mediumint(9) NOT NULL,
        section_type varchar(50) NOT NULL,
        section_data longtext NOT NULL,
        sort_order int(4) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY profile_id (profile_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_profiles );
    dbDelta( $sql_sections );
}
// Run this hook when the admin area loads to ensure tables exist
add_action( 'admin_init', 'nk_create_cv_builder_tables' );