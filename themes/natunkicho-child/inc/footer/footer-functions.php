<?php
/**
 * NatunKicho Smart Footer Engine
 * Handles Menus, Assets, and Partner Management
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NatunKicho_Footer_Engine {

    public function __construct() {
        add_action( 'init', array( $this, 'register_footer_menus' ) );
        add_action( 'init', array( $this, 'register_partner_cpt' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_footer_assets' ) );
    }

    // 1. Register the 6 Dynamic Menus
    public function register_footer_menus() {
        register_nav_menus( array(
            'footer-employers' => esc_html__( 'Footer - Employers', 'natunkicho' ),
            'footer-jobseekers'=> esc_html__( 'Footer - Job Seekers', 'natunkicho' ),
            'footer-learning'  => esc_html__( 'Footer - Learning', 'natunkicho' ),
            'footer-resources' => esc_html__( 'Footer - Resources', 'natunkicho' ),
            'footer-company'   => esc_html__( 'Footer - Company', 'natunkicho' ),
            'footer-support'   => esc_html__( 'Footer - Support', 'natunkicho' ),
        ) );
    }

    // 2. Load Modular CSS and JS (Only what's needed)
   public function enqueue_footer_assets() {
        wp_enqueue_style( 'nk-footer-style', get_stylesheet_directory_uri() . '/assets/css/footer.css', array(), '1.0.0', 'all' );
        wp_enqueue_script( 'nk-footer-script', get_stylesheet_directory_uri() . '/assets/js/footer.js', array(), '1.0.0', true );
        
        // Pass the WordPress AJAX URL to our footer JavaScript
        wp_localize_script( 'nk-footer-script', 'nkFooterAjax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
    }

    // 3. Register Custom Post Type for Partners (Clean Admin UI)
    public function register_partner_cpt() {
        $labels = array(
            'name'               => 'Footer Partners',
            'singular_name'      => 'Partner',
            'menu_name'          => 'Footer Partners',
            'add_new'            => 'Add New Partner',
            'add_new_item'       => 'Add New Partner Logo'
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false, // Hidden from frontend single pages
            'show_ui'             => true,
            'show_in_menu'        => 'themes.php', // Places it under Appearance
            'supports'            => array( 'title', 'thumbnail', 'custom-fields' ),
            'menu_icon'           => 'dashicons-networking',
        );
        register_post_type( 'nk_partner', $args );
    }
}

new NatunKicho_Footer_Engine();
/**
 * AJAX Handler: Connects Footer Newsletter to NK Email Engine
 */
add_action('wp_ajax_nk_footer_subscribe', 'nk_handle_footer_subscription');
add_action('wp_ajax_nopriv_nk_footer_subscribe', 'nk_handle_footer_subscription');

function nk_handle_footer_subscription() {
    // 1. Security Check
    check_ajax_referer('nk_newsletter_nonce', 'security');

    // 2. Validate Email & Name
    $name  = sanitize_text_field($_POST['name']); // Sanitize the new name field
    $email = sanitize_email($_POST['email']);
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
    }

    global $wpdb;
    
    // 3. EXACT Table Name from NK_Database::table('subscribers')
    $table_name = $wpdb->prefix . 'nk_subscribers'; 
    
    // 4. Check if the user is already subscribed to avoid duplicates
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email = %s", $email));
    
    if ($exists) {
        wp_send_json_error(array('message' => 'You are already on the list!'));
    }

    // 5. Insert directly into the NK Email Engine database
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'name'       => $name,  // Added Name to Database
            'email'      => $email,
            'status'     => 'active',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s', '%s') // Added one extra '%s' for the name format
    );

    if ($inserted) {
        wp_send_json_success(array('message' => 'Welcome to the NatunKicho Network! 🎉'));
    } else {
        wp_send_json_error(array('message' => 'DB Error: ' . $wpdb->last_error));
    }
}