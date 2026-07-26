<?php
/**
 * Plugin Name: NatunKicho Social Distributor
 * Plugin URI: https://natunkicho.com
 * Description: Enterprise-grade auto-distribution system for Jobs, Blogs, and Courses to multiple social networks with AI captions and analytics.
 * Version: 1.0.0
 * Author: NatunKicho 10x Dev Team
 * Text Domain: nk-social
 */

// Exit if accessed directly (Security First)
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Plugin Constants
define( 'NK_SOCIAL_VERSION', '1.0.0' );
define( 'NK_SOCIAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'NK_SOCIAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class NK_Social_Distributor
 * The main 10x Architect Class for the plugin.
 */
class NK_Social_Distributor {

   public function __construct() {
        // Register Activation Hook for Database creation
        register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
        
        // Load Core Classes
        require_once NK_SOCIAL_DIR . 'includes/class-nk-social-queue.php';
        require_once NK_SOCIAL_DIR . 'includes/class-nk-social-listener.php';
        require_once NK_SOCIAL_DIR . 'includes/class-nk-social-cron.php';
        require_once NK_SOCIAL_DIR . 'ai/class-nk-social-ai.php';

        // Load Platform Integrations
        require_once NK_SOCIAL_DIR . 'integrations/interface-nk-platform.php';
        require_once NK_SOCIAL_DIR . 'integrations/class-nk-telegram.php';
        require_once NK_SOCIAL_DIR . 'integrations/class-nk-linkedin.php';

        // Load Admin Dashboard UI if in WordPress backend
        if ( is_admin() ) {
            require_once NK_SOCIAL_DIR . 'admin/class-nk-social-admin.php';
        }
    }
    /**
     * Runs on plugin activation.
     * Safely creates our custom tables without locking the database.
     */
    public function activate_plugin() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Include WordPress upgrade file for dbDelta
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // 1. Create Queue Table
        $table_queue = $wpdb->prefix . 'nk_social_queue';
        $sql_queue = "CREATE TABLE $table_queue (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            content_id bigint(20) NOT NULL,
            content_type varchar(50) NOT NULL,
            platform varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            scheduled_time datetime NOT NULL,
            published_time datetime DEFAULT NULL,
            external_post_id varchar(255) DEFAULT NULL,
            error_message text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY scheduled_time (scheduled_time)
        ) $charset_collate;";
        dbDelta( $sql_queue );

        // 2. Create Analytics Table
        $table_analytics = $wpdb->prefix . 'nk_social_analytics';
        $sql_analytics = "CREATE TABLE $table_analytics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            content_id bigint(20) NOT NULL,
            platform varchar(50) NOT NULL,
            clicks int(11) NOT NULL DEFAULT 0,
            applications int(11) NOT NULL DEFAULT 0,
            published_date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY content_id (content_id),
            KEY platform (platform)
        ) $charset_collate;";
        dbDelta( $sql_analytics );
    }
}

// Initialize the Plugin Engine
new NK_Social_Distributor();