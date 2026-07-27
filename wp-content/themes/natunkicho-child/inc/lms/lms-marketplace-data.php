<?php
/**
 * Marketplace Data Engine
 * Registers Institutes and Tutors alongside Tutor LMS.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'nk_lms_register_marketplace_cpts' );
function nk_lms_register_marketplace_cpts() {
    
    // 1. Institutes (Universities, Partners)
    register_post_type( 'nk_institute', array(
        'labels' => array(
            'name'          => 'Institutes',
            'singular_name' => 'Institute',
            'menu_name'     => 'Institutes',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 31, // Places it right below Tutor LMS
        'menu_icon'     => 'dashicons-building',
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'show_in_rest'  => true,
    ) );

    // 2. Private Tutors
    register_post_type( 'nk_tutor', array(
        'labels' => array(
            'name'          => 'Private Tutors',
            'singular_name' => 'Tutor',
            'menu_name'     => 'Private Tutors',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 32,
        'menu_icon'     => 'dashicons-businessman',
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'show_in_rest'  => true,
    ) );
}

// 3. Custom Template Routing (Keeps the main theme root clean)
add_filter( 'single_template', 'nk_lms_load_custom_templates' );
function nk_lms_load_custom_templates( $single_template ) {
    global $post;

    // Route the Tutor Profile
    if ( $post->post_type === 'nk_tutor' ) {
        $custom_template = NK_LMS_DIR . 'templates/single-nk_tutor.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    // Route the Institute Profile
    if ( $post->post_type === 'nk_institute' ) {
        $custom_template = NK_LMS_DIR . 'templates/single-nk_institute.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    return $single_template;
}