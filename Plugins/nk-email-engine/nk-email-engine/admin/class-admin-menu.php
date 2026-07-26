<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Admin_Menu {

    const CAPABILITY = 'manage_options';

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        NK_Installer::maybe_upgrade();
    }

    public static function register_menu() {
        add_menu_page(
            'NK Email Engine',
            'NK Email Engine',
            self::CAPABILITY,
            'nk-email-engine',
            array( __CLASS__, 'render_dashboard' ),
            'dashicons-email-alt',
            58
        );

        add_submenu_page( 'nk-email-engine', 'Dashboard', 'Dashboard', self::CAPABILITY, 'nk-email-engine', array( __CLASS__, 'render_dashboard' ) );
        add_submenu_page( 'nk-email-engine', 'Subscribers', 'Subscribers', self::CAPABILITY, 'nk-email-subscribers', array( __CLASS__, 'render_subscribers' ) );
        add_submenu_page( 'nk-email-engine', 'Campaigns', 'Campaigns', self::CAPABILITY, 'nk-email-campaigns', array( __CLASS__, 'render_campaigns' ) );
        add_submenu_page( 'nk-email-engine', 'Templates', 'Templates', self::CAPABILITY, 'nk-email-templates', array( __CLASS__, 'render_templates' ) );
        add_submenu_page( 'nk-email-engine', 'Queue', 'Queue', self::CAPABILITY, 'nk-email-queue', array( __CLASS__, 'render_queue' ) );
        add_submenu_page( 'nk-email-engine', 'Analytics', 'Analytics', self::CAPABILITY, 'nk-email-analytics', array( __CLASS__, 'render_analytics' ) );
        add_submenu_page( 'nk-email-engine', 'Suppression List', 'Suppression List', self::CAPABILITY, 'nk-email-suppression', array( __CLASS__, 'render_suppression' ) );
        add_submenu_page( 'nk-email-engine', 'Provider Settings', 'Provider Settings', self::CAPABILITY, 'nk-email-settings', array( __CLASS__, 'render_settings' ) );
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'nk-email' ) === false ) {
            return;
        }
        wp_enqueue_style( 'nk-email-admin', NK_EMAIL_ENGINE_URL . 'assets/css/admin.css', array(), NK_EMAIL_ENGINE_VERSION );
        wp_enqueue_script( 'nk-email-admin', NK_EMAIL_ENGINE_URL . 'assets/js/admin.js', array( 'jquery' ), NK_EMAIL_ENGINE_VERSION, true );
    }

    private static function guard() {
        if ( ! current_user_can( self::CAPABILITY ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'nk-email-engine' ) );
        }
    }

    public static function render_dashboard()    { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/dashboard.php'; }
    public static function render_subscribers()   { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/subscribers.php'; }
    public static function render_campaigns()     { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/campaigns.php'; }
    public static function render_templates()     { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/templates.php'; }
    public static function render_queue()         { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/queue.php'; }
    public static function render_analytics()     { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/analytics.php'; }
    public static function render_suppression()   { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/suppression.php'; }
    public static function render_settings()      { self::guard(); require NK_EMAIL_ENGINE_PATH . 'admin/settings.php'; }
}
