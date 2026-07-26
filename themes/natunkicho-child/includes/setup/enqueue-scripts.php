<?php
/**
 * Natun Kicho Theme - Assets Manager
 * Complete version with all existing shortcodes + Mailchimp integration
 */

if (!defined('ABSPATH')) exit;

/**
 * OPTIMIZATION: Dequeue unused assets and optimize loading
 */
function nk_optimize_assets_loading() {
    // Only load single post assets on single blog posts
    if (!is_singular('post')) {
        wp_dequeue_style('nksp-single');
        wp_dequeue_script('nksp-single-js');
    }

    // Dequeue shortcode assets globally
    // They will load only when shortcode functions call them
    $shortcode_assets = array(
        'nk-product-slider',
        'nk-product-slider-js',
        'nk-dynamic-menu-section',
        'nk-dynamic-menu-section-js',
        'nk-category-grid',
        'nk-category-grid-js',
        'nk-category-grid-style2',
        'nk-category-grid-style2-js',
        'nk-hero-slider',
        'nk-hero-slider-js',
        'nk-dropdown-menu',
        'nk-dropdown-menu-js'
    );

    foreach ($shortcode_assets as $asset) {
        wp_dequeue_style($asset);
        wp_dequeue_script($asset);
    }
}
add_action('wp_enqueue_scripts', 'nk_optimize_assets_loading', 100);

/**
 * Enqueue Parent + Child Theme Styles
 */
function nk_enqueue_theme_styles() {
    // Astra Parent Style
    wp_enqueue_style(
        'astra-theme-css',
        get_template_directory_uri() . '/style.css',
        array(),
        filemtime(get_template_directory() . '/style.css')
    );

    // Child Theme Main Style
    wp_enqueue_style(
        'natun-kicho-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-theme-css'),
        filemtime(get_stylesheet_directory() . '/style.css')
    );
    
    wp_enqueue_style(
        'nk-employer',
        get_stylesheet_directory_uri() . '/assets/css/employer.css',
        [],
        time()
    );
}
add_action('wp_enqueue_scripts', 'nk_enqueue_theme_styles', 5);

/**
 * Register Global Theme Assets
 */
function nk_register_global_assets() {
    nk_register_asset('nk-home', '/assets/css/home.css');
    nk_register_asset('nk-jobs', '/assets/css/jobs.css');
    nk_register_asset('nk-dashboard', '/assets/css/dashboard.css');
    nk_register_asset('nk-profile', '/assets/css/profile.css');
    nk_register_asset('nk-global-search', '/assets/css/global-search.css');
    nk_register_asset('nk-responsive', '/assets/css/responsive.css');
    nk_register_asset('nk-ai', '/assets/css/ai.css');
}
add_action('wp_enqueue_scripts', 'nk_register_global_assets', 6);

/**
 * Enqueue Global Theme Assets
 */
function nk_enqueue_global_assets() {
    wp_enqueue_style('nk-home');
    wp_enqueue_style('nk-jobs');
    wp_enqueue_style('nk-dashboard');
    wp_enqueue_style('nk-profile');
    wp_enqueue_style('nk-global-search');
    wp_enqueue_style('nk-responsive');
    wp_enqueue_style('nk-ai');
}
add_action('wp_enqueue_scripts', 'nk_enqueue_global_assets', 20);

/**
 * Register Styles & Scripts Helper
 */
function nk_register_asset($handle, $path, $deps = array(), $in_footer = false) {
    $full_path = get_stylesheet_directory() . $path;
    $uri_path  = get_stylesheet_directory_uri() . $path;

    if (file_exists($full_path)) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $version = filemtime($full_path);

        if ($ext === 'css') {
            wp_register_style($handle, $uri_path, $deps, $version);
        } elseif ($ext === 'js') {
            wp_register_script($handle, $uri_path, $deps, $version, $in_footer);
        }
    }
}

/**
 * Register All Theme Assets (EXISTING + NEW)
 */
function nk_register_theme_assets() {
    // === EXISTING ASSETS ===
    
    nk_register_asset('nk-post-grid', '/assets/css/post-grid-section.css');
    nk_register_asset('nk-post-grid-js', '/assets/js/post-grid-section.js', array('jquery'), true);

    nk_register_asset('nk-product-slider', '/template-parts/product-slider/nk-product-slider.css');
    nk_register_asset('nk-product-slider-js', '/template-parts/product-slider/nk-product-slider.js', array(), true);

    nk_register_asset('nk-dynamic-menu-section', '/assets/css/nk-dynamic-menu-section.css');
    nk_register_asset('nk-dynamic-menu-section-js', '/assets/js/nk-dynamic-menu-section.js', array('jquery'), true);

    nk_register_asset('nk-category-grid', '/assets/css/nk-category-grid.css');
    nk_register_asset('nk-category-grid-js', '/assets/js/nk-category-grid.js', array('jquery'), true);

    nk_register_asset('nk-category-grid-style2', '/assets/css/nk-category-grid-style2.css');
    nk_register_asset('nk-category-grid-style2-js', '/assets/js/nk-category-grid-style2.js', array('jquery'), true);

    nk_register_asset('nk-hero-slider', '/assets/css/nk-hero-slider.css');
    nk_register_asset('nk-hero-slider-js', '/assets/js/nk-hero-slider.js', array('jquery'), true);

    nk_register_asset('nk-dropdown-menu', '/assets/css/dropdown-menu.css');
    nk_register_asset('nk-dropdown-menu-js', '/assets/js/nk-dropdown-menu.js', array(), true);

    // === NEW SINGLE POST ASSETS ===
    nk_register_asset('nksp-single', '/assets/css/nksp-single.min.css');
    nk_register_asset('nksp-single-js', '/assets/js/nksp-single.min.js', array('jquery'), true);
    
    // Contact Form
    nk_register_asset('nk-contact-form', '/assets/css/nk-contact-form.css');
    nk_register_asset('nk-contact-form-js', '/assets/js/nk-contact-form.js', array('jquery'), true);
    
    // === NEW: SaaS Marketplace Asset ===
    nk_register_asset('nk-marketplace', '/assets/css/marketplace.css');
}
add_action('wp_enqueue_scripts', 'nk_register_theme_assets', 5);

/**
 * Job Save Script
 */
function nk_enqueue_job_save_script() {
    if (!is_user_logged_in()) {
        return;
    }

    wp_enqueue_script(
        'nk-job-save',
        get_stylesheet_directory_uri() . '/assets/js/nk-job-save.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('nk-job-save', 'nkJob', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('nk_save_job_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'nk_enqueue_job_save_script');

/**
 * Enqueue Functions for Shortcodes (EXISTING)
 */
function nk_enqueue_product_slider_assets() {
    wp_enqueue_style('nk-product-slider');
    wp_enqueue_script('nk-product-slider-js');
}

function nk_enqueue_dynamic_menu_assets() {
    wp_enqueue_style('nk-dynamic-menu-section');
    wp_enqueue_script('nk-dynamic-menu-section-js');
}

function nk_enqueue_category_grid_assets() {
    wp_enqueue_style('nk-category-grid');
    wp_enqueue_script('nk-category-grid-js');
}

function nk_enqueue_category_grid_style2_assets() {
    wp_enqueue_style('nk-category-grid-style2');
    wp_enqueue_script('nk-category-grid-style2-js');
}

function nk_enqueue_hero_slider_assets() {
    wp_enqueue_style('nk-hero-slider');
    wp_enqueue_script('nk-hero-slider-js');
}

function nk_enqueue_dropdown_menu_assets() {
    wp_enqueue_style('nk-dropdown-menu');
    wp_enqueue_script('nk-dropdown-menu-js');
}

// === NEW: Single Post Assets ===
function nk_enqueue_single_post_assets() {
    if (is_single()) {
        wp_enqueue_style('nksp-single');
        wp_enqueue_script('nksp-single-js');
    }
}
add_action('wp_enqueue_scripts', 'nk_enqueue_single_post_assets', 15);

// === NEW: SaaS Marketplace Assets ===
function nk_enqueue_marketplace_assets() {
    if ( is_page_template( 'page-marketplace.php' ) ) {
        wp_enqueue_style( 'nk-marketplace' );
    }
}
add_action('wp_enqueue_scripts', 'nk_enqueue_marketplace_assets', 15);

/**
 * === NEW: Global Header Assets (CSS, JS, FontAwesome & AJAX) ===
 */
function nk_enqueue_header_core_assets() {
    // 1. Header CSS
    wp_enqueue_style('natunkicho-header-css', get_stylesheet_directory_uri() . '/assets/css/header.css', array(), '1.0.0');

    // 2. Header JS
    wp_enqueue_script('natunkicho-header-js', get_stylesheet_directory_uri() . '/assets/js/header.js', array('jquery'), '1.0.0', true);

    // 3. Localize script for AJAX (Critical for notifications and user menus)
    wp_localize_script('natunkicho-header-js', 'natunkicho_ajax', array(
        'ajax_url'  => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('natunkicho_nonce'),
        'logged_in' => is_user_logged_in() ? '1' : '0',
        'user_role' => is_user_logged_in() && !empty(wp_get_current_user()->roles) ? wp_get_current_user()->roles[0] : 'guest',
    ));

    // 4. Font Awesome (Required for utility bar icons, search, etc.)
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
}
add_action('wp_enqueue_scripts', 'nk_enqueue_header_core_assets', 15);

/**
 * Load Shortcodes & Integrations (UPDATED)
 */
function nk_load_theme_shortcodes() {
    $shortcodes = array(
        '/includes/shortcodes/nk-dynamic-menu-section.php' => 'Dynamic Menu Section',
        '/includes/shortcodes/post-grid-shortcode.php' => 'Post Grid',
        '/includes/shortcodes/nk-category-grid-shortcode.php' => 'Category Grid',
        '/includes/shortcodes/nk-category-grid-style2.php' => 'Category Grid Style 2',
        '/includes/shortcodes/nk-hero-slider.php' => 'Hero Slider',
        '/includes/shortcodes/dropdown-menu.php' => 'Dropdown Menu',
        '/includes/shortcodes/nk-footer-slider.php' => 'Footer Slider',
        '/includes/shortcodes/nk-contact-form.php' => 'Contact Form',
        '/includes/shortcodes/latest-jobs-widget.php' => 'Latest Jobs Widget',
        
        // 🔴 SAAS ADDITIONS
        '/includes/mailchimp-handler.php' => 'Mailchimp Job Alerts Engine'
    );

    foreach ($shortcodes as $file_path => $shortcode_name) {
        $full_path = get_stylesheet_directory() . $file_path;
        if (file_exists($full_path)) {
            require_once $full_path;
        }
    }
}
add_action('init', 'nk_load_theme_shortcodes');