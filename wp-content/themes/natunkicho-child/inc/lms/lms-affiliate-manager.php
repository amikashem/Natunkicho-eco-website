<?php
/**
 * Natunkicho Affiliate Course Manager
 * Creates a fast bulk-edit screen for external links, bypassing the React Builder.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Add it to the WordPress Menu under Tutor LMS
add_action( 'admin_menu', 'nk_affiliate_manager_menu' );
function nk_affiliate_manager_menu() {
    add_submenu_page(
        'tutor',               // Parent Menu (Puts it inside Tutor LMS)
        'Affiliate Links',     // Page Title
        'Affiliate Links',     // Menu Title
        'manage_options',      // Capability required
        'nk-affiliate-manager',// Menu Slug
        'nk_affiliate_manager_page'
    );
}

// 2. Build the User Interface
function nk_affiliate_manager_page() {
    // Check if the user hit the Save button
    if ( isset( $_POST['nk_save_links'] ) ) {
        foreach ( $_POST['ext_link'] as $post_id => $link ) {
            update_post_meta( $post_id, '_nk_external_link', esc_url_raw( $link ) );
        }
        foreach ( $_POST['btn_text'] as $post_id => $text ) {
            update_post_meta( $post_id, '_nk_button_text', sanitize_text_field( $text ) );
        }
        echo '<div class="notice notice-success is-dismissible"><p><strong>Links Saved Successfully!</strong></p></div>';
    }

    echo '<div class="wrap">';
    echo '<h1 style="margin-bottom: 15px;">Natunkicho Affiliate Course Manager</h1>';
    echo '<p style="font-size: 14px; color: #555; margin-bottom: 20px;">Paste your external Coursera, Udemy, or Partner links here. This bypasses the heavy Course Builder so you can edit links instantly.</p>';

    // Fetch all courses
    $courses = new WP_Query( array(
        'post_type'      => 'courses',
        'posts_per_page' => -1, // Get all of them
        'post_status'    => array('publish', 'draft')
    ) );

    echo '<form method="post">';
    echo '<table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">';
    echo '<thead><tr>
            <th style="width: 40%;">Course Name</th>
            <th style="width: 40%;">External / Affiliate URL</th>
            <th style="width: 20%;">Button Text</th>
          </tr></thead><tbody>';

    if ( $courses->have_posts() ) {
        while ( $courses->have_posts() ) {
            $courses->the_post();
            $post_id = get_the_ID();
            
            // Get current data
            $link = get_post_meta( $post_id, '_nk_external_link', true );
            $btn  = get_post_meta( $post_id, '_nk_button_text', true ) ?: 'Go to Partner Site';
            
            echo '<tr>';
            echo '<td><strong>' . get_the_title() . '</strong><br><span style="color:#888; font-size:11px;">Status: ' . get_post_status() . '</span></td>';
            echo '<td><input type="url" name="ext_link['.$post_id.']" value="'.esc_attr($link).'" style="width:100%;" placeholder="https://www.coursera.org/..."></td>';
            echo '<td><input type="text" name="btn_text['.$post_id.']" value="'.esc_attr($btn).'" style="width:100%;"></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">No courses found. Go create a course first!</td></tr>';
    }
    
    echo '</tbody></table>';
    echo '<input type="submit" name="nk_save_links" class="button button-primary button-large" value="Save All Links">';
    echo '</form></div>';
}