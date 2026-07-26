<?php
/**
 * Template Name: SaaS Homepage (NatunKicho)
 * Description: The strict dynamic router with Active View Memory.
 */

// Prevent caching on this dynamic router for logged-in users
if ( is_user_logged_in() && !defined('DONOTCACHEPAGE') ) {
    define('DONOTCACHEPAGE', true);
}

get_header(); 

if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $roles = (array) $current_user->roles;
    
    // 1. SAAS FEATURE: Handle Manual View Switching
    // If you ever want to add a "Switch to Candidate View" button, just link it to: yoursite.com/?switch_view=candidate
    if ( isset($_GET['switch_view']) ) {
        $requested_view = sanitize_text_field($_GET['switch_view']);
        
        // Security: Only allow them to switch if they actually have that role (or if they are an Admin)
        if ( in_array($requested_view, $roles) || in_array('administrator', $roles) ) {
            update_user_meta( $current_user->ID, '_nk_active_view', $requested_view );
        }
    }

    // 2. Determine the Active Role to Display
    // First, check if they have a saved preference in the database from switching
    $active_view = get_user_meta( $current_user->ID, '_nk_active_view', true );
    
    // If no preference is saved, OR they lost that role, use their Primary Role
    if ( empty($active_view) || (!in_array($active_view, $roles) && !in_array('administrator', $roles)) ) {
        // WordPress always puts the Primary Role at index 0 of the array
        $active_view = !empty($roles) ? $roles[0] : 'subscriber'; 
    }

    // 3. Route to the exact template based on the definitive $active_view
    if ( $active_view === 'administrator' || $active_view === 'employer' ) {
        get_template_part( 'template-parts/home/employer' );
    } 
    elseif ( $active_view === 'wholesale' || $active_view === 'b2b' ) {
        get_template_part( 'template-parts/home/wholesale' );
    } 
    elseif ( $active_view === 'student' ) {
        get_template_part( 'template-parts/home/student' );
    } 
    else {
        // Default fallback for Candidate, Subscriber, or Guest
        get_template_part( 'template-parts/home/candidate' );
    }

} else {
    // Guest gets the normal website marketing page
    get_template_part( 'template-parts/home/guest' );
}

get_footer(); 
?>