<?php
/**
 * Handles the WordPress Admin Dashboard UI
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Social_Admin {

    public function __construct() {
        // Hook into the admin menu
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        // Register settings in the database
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_plugin_page() {
        add_menu_page(
            'Social Distributor',           // Page title
            'Social Auto-Post',             // Menu title (Shows in sidebar)
            'manage_options',               // Required user capability
            'nk-social-distributor',        // Menu slug
            array( $this, 'create_admin_page' ), // Callback function
            'dashicons-share',              // Icon (Share icon)
            30                              // Position in menu
        );
    }

    public function register_settings() {
        // Registers a single array in the wp_options table to hold all API keys
        register_setting( 'nk_social_option_group', 'nk_social_settings' );
    }

    public function create_admin_page() {
        // Get the active tab, default to 'settings'
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
        
        echo '<div class="wrap">';
        echo '<h1>NatunKicho Social Distributor 🚀</h1>';
        
        // Navigation Tabs
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=nk-social-distributor&tab=settings" class="nav-tab ' . ( $active_tab == 'settings' ? 'nav-tab-active' : '' ) . '">API Settings</a>';
        echo '<a href="?page=nk-social-distributor&tab=queue" class="nav-tab ' . ( $active_tab == 'queue' ? 'nav-tab-active' : '' ) . '">Publish Queue</a>';
        echo '<a href="?page=nk-social-distributor&tab=analytics" class="nav-tab ' . ( $active_tab == 'analytics' ? 'nav-tab-active' : '' ) . '">Analytics</a>';
        echo '</h2>';

        // Load the correct view based on the tab
        if ( $active_tab == 'settings' ) {
            include_once plugin_dir_path( __FILE__ ) . 'views/view-settings.php';
        } elseif ( $active_tab == 'queue' ) {
            include_once plugin_dir_path( __FILE__ ) . 'views/view-queue.php';
        } elseif ( $active_tab == 'analytics' ) {
            echo '<div class="notice notice-info"><p><strong>Analytics Dashboard:</strong> This will be built in Phase 4. It will show charts for clicks, applications, and traffic sources.</p></div>';
        }
        
        echo '</div>';
    }
}

// Initialize the Admin Class
new NK_Social_Admin();