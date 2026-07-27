<?php
/**
 * Plugin Name: NK Email Intelligence Engine
 * Plugin URI:  https://natunkicho.com
 * Description: Centralized email management system for NatunKicho Job Portal, Hospitality Blogs, Recipes, Training Content, and Marketing Campaigns. Handles subscribers, queued sending, multi-provider delivery (Amazon SES / Brevo), templates, suppression list, and analytics.
 * Version:     1.0.0
 * Author:      NatunKicho
 * Text Domain: nk-email-engine
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access.
}

define( 'NK_EMAIL_ENGINE_VERSION', '1.0.0' );
define( 'NK_EMAIL_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'NK_EMAIL_ENGINE_URL', plugin_dir_url( __FILE__ ) );
define( 'NK_EMAIL_ENGINE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Core loader. Everything else is required from here so load order is explicit
 * and predictable (database/installer first, providers before the queue that
 * depends on them, admin screens last).
 */
final class NK_Email_Engine {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->hooks();
    }

    private function includes() {
        // Core
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-database.php';
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-installer.php';
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-email-logger.php';
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-provider-manager.php';
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-template-manager.php';
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-subscriber-manager.php';
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-email-queue.php';

        // Providers
        require_once NK_EMAIL_ENGINE_PATH . 'providers/interface-nk-email-provider.php';
        require_once NK_EMAIL_ENGINE_PATH . 'providers/class-amazon-ses.php';
        require_once NK_EMAIL_ENGINE_PATH . 'providers/class-brevo.php';

        // Cron
        require_once NK_EMAIL_ENGINE_PATH . 'cron/process-email-queue.php';

        // Admin (only in wp-admin, no need to load on the frontend)
        if ( is_admin() ) {
            require_once NK_EMAIL_ENGINE_PATH . 'admin/class-admin-menu.php';
        }

        // Public-facing shortcode
        require_once NK_EMAIL_ENGINE_PATH . 'includes/class-shortcodes.php';
    }

    private function hooks() {
        register_activation_hook( __FILE__, array( 'NK_Installer', 'activate' ) );
        register_deactivation_hook( __FILE__, array( 'NK_Installer', 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        if ( is_admin() ) {
            add_action( 'init', array( 'NK_Admin_Menu', 'init' ) );
        }

        // Cron hook -> queue processor
        add_action( 'nk_process_email_queue', array( 'NK_Cron_Queue_Processor', 'run' ) );

        // Shortcode
        add_action( 'init', array( 'NK_Shortcodes', 'init' ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'nk-email-engine', false, dirname( NK_EMAIL_ENGINE_BASENAME ) . '/languages' );
    }
}

// ==============================================================================
// 10X WEEKLY AUTOPILOT (Automated Job Alert Blast)
// ==============================================================================

// 1. Register a custom 'weekly' schedule interval in WordPress
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['weekly'] = array(
        'interval' => 604800, // 7 days in seconds
        'display'  => __( 'Once Weekly', 'nk-email-engine' )
    );
    return $schedules;
});

// 2. Schedule the event to run automatically if it isn't already scheduled
add_action('wp', function() {
    if ( ! wp_next_scheduled( 'nk_automated_weekly_blast' ) ) {
        wp_schedule_event( time(), 'weekly', 'nk_automated_weekly_blast' );
    }
});

// 3. The logic that runs every week
add_action( 'nk_automated_weekly_blast', function() {
    global $wpdb;
    
    // NOTE: You must know the ID of your "Smart Job Match" Template!
    // For this example, let's assume your Template ID is 2. Change this ID as needed.
    $template_id = 2; 

    if ( class_exists('NK_Template_Manager') && class_exists('NK_Database') ) {
        $tpl = NK_Template_Manager::get( $template_id );
        
        if ( $tpl ) {
            $table_queue = NK_Database::table( 'email_queue' );
            $table_subs  = NK_Database::table( 'subscribers' );
            $unsub_base  = home_url( '/?nk_unsubscribe=' );
            $time_now    = current_time( 'mysql' );
            
            // Blast only to active users interested in 'candidate' or 'jobs'
            $sql = $wpdb->prepare( "
                INSERT INTO {$table_queue} (recipient_email, recipient_name, subject, body, priority, status, created_at, scheduled_at)
                SELECT email, name, 
                    REPLACE(REPLACE(%s, '{{name}}', name), '{{email}}', email),
                    REPLACE(REPLACE(REPLACE(%s, '{{name}}', name), '{{email}}', email), '{{unsubscribe_link}}', CONCAT(%s, unsubscribe_token)),
                    'normal', 'pending', %s, %s
                FROM {$table_subs}
                WHERE status = 'active' AND (interests = 'candidate' OR interests = 'jobs')
            ", $tpl['subject'], $tpl['html_content'], $unsub_base, $time_now, $time_now );

            $wpdb->query( $sql );
            // The queue is now full. The 1-minute server cron will start pushing them to Amazon SES automatically!
        }
    }
});

NK_Email_Engine::instance();
