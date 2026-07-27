<?php
/**
 * Natunkicho SliceWP Integration
 * Injects the SliceWP Affiliate Dashboard directly into the Tutor LMS Dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Add the "Affiliate Earnings" tab to the Tutor LMS Dashboard Menu
add_filter( 'tutor_dashboard_pages', 'nk_add_slicewp_to_tutor_menu' );
function nk_add_slicewp_to_tutor_menu( $pages ) {
    
    // We insert it right before the 'Settings' tab for a logical flow
    $new_pages = array();
    foreach ( $pages as $key => $page ) {
        if ( $key === 'settings' ) {
            $new_pages['affiliate-dashboard'] = array(
                'title' => 'Affiliate Earnings',
                'icon'  => 'tutor-icon-chart-bar', // Premium icon
            );
        }
        $new_pages[$key] = $page;
    }
    return $new_pages;
}

// 2. Render the SliceWP content when the tab is clicked
add_action( 'tutor_load_dashboard_template', 'nk_render_slicewp_in_tutor' );
function nk_render_slicewp_in_tutor( $template ) {
    global $wp_query;
    $query_var = $wp_query->query_vars['tutor_dashboard_page'];

    if ( $query_var === 'affiliate-dashboard' ) {
        echo '<div class="tutor-dashboard-content-inner">';
        echo '<h3 style="margin-bottom: 20px;">Your Affiliate Headquarters</h3>';
        echo '<p style="color: #666; margin-bottom: 30px;">Track your referrals, copy your custom links, and view your payout history below.</p>';
        
        // Execute the SliceWP Shortcode inside Tutor LMS
        echo do_shortcode( '[slicewp_affiliate_dashboard]' );
        
        echo '</div>';
        
        // Prevent Tutor LMS from trying to load a non-existent template file
        return true; 
    }
    
    return $template;
}