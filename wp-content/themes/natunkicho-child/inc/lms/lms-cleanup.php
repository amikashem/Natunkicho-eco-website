<?php
/**
 * Tutor LMS Cleanup
 * Optimizes the LMS for a "Link Directory" rather than a video hosting platform.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Remove the "Video" and "Attachments" tabs from the Frontend Course Builder
add_filter( 'tutor_course_builder_settings_tabs', 'nk_clean_tutor_builder_tabs', 10, 1 );
function nk_clean_tutor_builder_tabs( $tabs ) {
    
    // Hide Video Uploads (Saves your server bandwidth)
    if ( isset( $tabs['video'] ) ) {
        unset( $tabs['video'] );
    }
    
    // Hide File Attachments (Optional, but keeps the builder clean)
    if ( isset( $tabs['attachments'] ) ) {
        unset( $tabs['attachments'] );
    }
    
    return $tabs;
}

// 2. Disable default Q&A feature globally (since users take courses off-site on Coursera/Udemy)
add_filter( 'tutor_enable_qa', '__return_false' );

// 3. Hide the video metabox in the standard WordPress Backend (Admin area)
add_action( 'admin_head', 'nk_hide_tutor_backend_clutter' );
function nk_hide_tutor_backend_clutter() {
    echo '<style>
        #tutor-course-video-meta { display: none !important; }
    </style>';
}

// 4. Aggressively hide "Upgrade to Pro" nags, badges, and menu items
add_action( 'admin_head', 'nk_hide_tutor_pro_nags' );
function nk_hide_tutor_pro_nags() {
    echo '<style>
        .tutor-admin-pro-badge,
        .tutor-upgrade-pro,
        #tutor-menu-upgrade,
        #tutor-pro-upgrade,
        a[href*="tutor-pro"],
        a[href*="themeum.com/product/tutor-lms"],
        .tutor-dashboard-builder-pro-badge,
        li.tutor-pro-menu { 
            display: none !important; 
        }
    </style>';
}

// 5. Block standard users from accessing the WordPress backend
/*add_action( 'init', 'nk_block_wp_admin_access' );
function nk_block_wp_admin_access() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) && ! current_user_can( 'manage_options' ) ) {
        
        // If they have Tutor LMS active, send them to the frontend learning dashboard
        if ( function_exists( 'tutor_utils' ) ) {
            wp_safe_redirect( tutor_utils()->tutor_dashboard_url() );
            exit;
        } else {
            // Fallback to the homepage
            wp_safe_redirect( home_url() );
            exit;
        }
    }
} */