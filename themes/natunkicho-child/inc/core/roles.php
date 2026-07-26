<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * CORE ROLES & WORKSPACE CONTEXT SWITCHER SYSTEM
 * Path: inc/core/roles.php
 * Handles user role generation, workspace toggling, and monetization filters.
 * =========================================================================
 */

/**
 * 1. Ensure core system roles and premium capabilities are initialized in the database
 */
function nk_init_roles() {
    // Standard Free Roles
    add_role('job_seeker', 'Job Seeker', ['read' => true]);
    add_role('employer', 'Employer', ['read' => true]);

    // Premium Roles with Custom Capabilities
    add_role('premium_job_seeker', 'Premium Job Seeker', [
        'read'                => true,
        'nk_priority_apply'   => true,
        'nk_unlimited_alerts' => true,
        'nk_ai_assistance'    => true
    ]);

    add_role('premium_employer', 'Premium Employer', [
        'read'                      => true,
        'nk_view_full_cv'           => true,
        'nk_download_resume'        => true,
        'nk_direct_message'         => true,
        'nk_talent_database_access' => true
    ]);
}
add_action('init', 'nk_init_roles');

/**
 * 2. Retrieve the user's active workspace view selection
 */
function nk_get_active_workspace( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return 'candidate';
    }

    $current_context = get_user_meta( $user_id, 'nk_active_workspace', true );
    
    // Default fallback based on their primary account type assignment
    if ( empty( $current_context ) ) {
        $user_obj = get_userdata( $user_id );
        // Check for either free or premium employer role
        if ( $user_obj && ( in_array( 'employer', (array) $user_obj->roles ) || in_array( 'premium_employer', (array) $user_obj->roles ) ) ) {
            $current_context = 'employer';
        } else {
            $current_context = 'candidate';
        }
        update_user_meta( $user_id, 'nk_active_workspace', $current_context );
    }

    return $current_context;
}

/**
 * 3. AJAX Gateway: Handles instant, secure environment switching
 */
function nk_ajax_switch_user_workspace() {
    check_ajax_referer( 'nk_workspace_nonce', 'security' );
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Session expired. Please log in first.' );
    }

    $user_id = get_current_user_id();
    $target_context = sanitize_text_field( $_POST['target_context'] ); // 'candidate' or 'employer'

    if ( ! in_array( $target_context, ['candidate', 'employer'] ) ) {
        wp_send_json_error( 'Invalid target workspace view environment requested.' );
    }

    $user_obj = get_userdata( $user_id );
    $roles    = (array) $user_obj->roles;

    // --- FUTURE MONETIZATION FILTER GATEWAY ---
    $is_allowed_to_switch = apply_filters( 'nk_can_user_switch_workspace', true, $user_id, $target_context );
    
    if ( ! $is_allowed_to_switch ) {
        wp_send_json_error( 'Upgrading to an Employer profile workspace requires an active premium subscription tier.' );
    }

    // Account Elevation: If a candidate switches to recruiter view, dynamically add the base employer role
    if ( $target_context === 'employer' && ! in_array( 'employer', $roles ) && ! in_array( 'premium_employer', $roles ) ) {
        $user_obj->add_role( 'employer' );
        if ( empty( get_user_meta( $user_id, 'nk_company_name', true ) ) ) {
            update_user_meta( $user_id, 'nk_company_name', $user_obj->display_name );
        }
    }

    // Save the context choice to user memory
    update_user_meta( $user_id, 'nk_active_workspace', $target_context );
    
    wp_send_json_success([
        'message' => 'Workspace switched smoothly!',
        'redirect' => home_url( '/dashboard/' )
    ]);
}
add_action( 'wp_ajax_nk_switch_user_workspace', 'nk_ajax_switch_user_workspace' );

/**
 * 4. Centralized Premium Status Checker
 * Use this function throughout the theme to check if a user should see premium features.
 */
function nk_is_user_premium( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return false;
    }

    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return false;
    }

    $roles = (array) $user->roles;

    // Admins get automatic premium access for testing purposes
    if ( in_array( 'administrator', $roles ) ) {
        return true;
    }

    // Check if the user has been granted a premium role
    if ( in_array( 'premium_job_seeker', $roles ) || in_array( 'premium_employer', $roles ) ) {
        return true;
    }

    // Fallback: Check for an active premium user meta flag (Hooks directly into WooCommerce Subscriptions later)
    $is_premium_meta = get_user_meta( $user_id, 'nk_is_premium', true );
    return $is_premium_meta === 'yes';
}