<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Force Fix: WP Job Manager Category 404 Error
add_action( 'init', 'nk_force_fix_job_categories', 999 );
function nk_force_fix_job_categories() {
    global $wp_taxonomies;
    
    // Safely check and initialize taxonomy rewrite properties
    if ( isset( $wp_taxonomies['job_listing_category'] ) ) {
        if ( empty( $wp_taxonomies['job_listing_category']->rewrite ) || ! is_array( $wp_taxonomies['job_listing_category']->rewrite ) ) {
            $wp_taxonomies['job_listing_category']->rewrite = array();
        }
        $wp_taxonomies['job_listing_category']->rewrite['slug'] = 'job-category';
        $wp_taxonomies['job_listing_category']->rewrite['with_front'] = false;
    }
}

add_action( 'admin_init', function() {
    if ( ! get_transient( 'nk_hard_flush_categories' ) ) {
        flush_rewrite_rules( false );
        set_transient( 'nk_hard_flush_categories', true, 12 * HOUR_IN_SECONDS );
    }
}); 

// Job Country Taxonomy & Rewrite Fix
add_action( 'init', 'nk_register_job_country_taxonomy', 0 );
function nk_register_job_country_taxonomy() {
    if ( ! taxonomy_exists( 'job_country' ) ) {
        $labels = array(
            'name'              => _x( 'Countries', 'taxonomy general name', 'natunkicho' ),
            'singular_name'     => _x( 'Country', 'taxonomy singular name', 'natunkicho' ),
            'search_items'      => __( 'Search Countries', 'natunkicho' ),
            'all_items'         => __( 'All Countries', 'natunkicho' ),
            'menu_name'         => __( 'Job Countries', 'natunkicho' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'country', 'with_front' => false ),
        );

        register_taxonomy( 'job_country', array( 'job_listing' ), $args );
    }
}

add_action( 'init', 'nk_force_fix_job_country_slug', 999 );
function nk_force_fix_job_country_slug() {
    global $wp_taxonomies;
    
    // Safely check and initialize taxonomy rewrite properties
    if ( isset( $wp_taxonomies['job_listing_region'] ) ) {
        if ( empty( $wp_taxonomies['job_listing_region']->rewrite ) || ! is_array( $wp_taxonomies['job_listing_region']->rewrite ) ) {
            $wp_taxonomies['job_listing_region']->rewrite = array();
        }
        $wp_taxonomies['job_listing_region']->rewrite['slug'] = 'country';
        $wp_taxonomies['job_listing_region']->rewrite['with_front'] = false;
    }
}