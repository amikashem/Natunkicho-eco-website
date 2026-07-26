<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================
 * CANDIDATE EASY APPLY SYSTEM (Synchronized)
 * Path: inc/jobs/jobs-apply.php
 * =========================================
 */

/**
 * APPLY BUTTON SHORTCODE (Upgraded Dual-Stack)
 */
/**
 * APPLY BUTTON SHORTCODE (Upgraded Dual-Stack & Bulletproof)
 */
function nk_easy_apply_button( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '<a class="nk-login-apply" style="display:block; text-align:center; background:#0A66C2; color:#fff; padding:14px; border-radius:8px; font-weight:bold; text-decoration:none;" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Login to Apply</a>';
    }

    $atts = shortcode_atts( [ 'job_id' => get_the_ID() ], $atts );
    $job_id = absint( $atts['job_id'] );

    if ( get_post_type( $job_id ) !== 'job_listing' ) return '';

    $user_id = get_current_user_id();
    
    // --- 10x FIX: Aggressively search for the External URL ---
    $ext_url = get_post_meta( $job_id, '_application_url', true );
    
    // Fallback: If it's empty, check the default WPJM '_application' field to see if they pasted a URL there instead of an email
    if ( empty( $ext_url ) ) {
        $fallback_url = get_post_meta( $job_id, '_application', true );
        if ( ! empty( $fallback_url ) && ! is_email( $fallback_url ) && filter_var( $fallback_url, FILTER_VALIDATE_URL ) ) {
            $ext_url = $fallback_url;
        }
    }
    
    // Check if applied using the user meta
    $applied_jobs = get_user_meta( $user_id, 'nk_applied_jobs', true );
    $applied_jobs = is_array( $applied_jobs ) ? $applied_jobs : [];
    $already_applied = in_array( $job_id, $applied_jobs );

    ob_start();
    ?>
    <div class="nk-easy-apply-wrapper" style="display: flex; flex-direction: column; gap: 12px; width: 100%; margin-top: 15px;">
        
        <?php if ( $already_applied ) : ?>
            <button class="nk-apply-btn applied" disabled style="width:100%; padding:15px; border-radius:8px; background:#94a3b8; color:#fff; border:none; font-weight:700; font-size:16px; cursor:not-allowed;">Already Applied</button>
        <?php else : ?>
            <button class="nk-apply-btn" data-job="<?php echo esc_attr( $job_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'nk_easy_apply_nonce' ) ); ?>" style="width:100%; padding:15px; border-radius:8px; background:#0A66C2; color:#fff; border:none; font-weight:700; font-size:16px; cursor:pointer; transition:0.2s;">
                Easy Apply
            </button>
        <?php endif; ?>

        <?php 
        // 10x FIX: Removed the strict 'is_singular' check so it forces the button to render no matter what theme template you use!
        if ( ! empty( $ext_url ) ) : ?>
            <a href="<?php echo esc_url( $ext_url ); ?>" target="_blank" rel="noopener noreferrer" class="nk-btn-external" style="width:100%; padding:13px; border-radius:8px; background:#fff; color:#0A66C2; border:2px solid #0A66C2; font-weight:700; font-size:16px; text-align:center; text-decoration:none; box-sizing:border-box; display:block; transition:0.2s;">
                Apply on Company Website
            </a>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_easy_apply', 'nk_easy_apply_button' ); // We will use this global shortcode now

/**
 * APPLY AJAX HANDLER
 */
function nk_easy_apply_ajax() {
    check_ajax_referer( 'nk_easy_apply_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) wp_send_json( [ 'success' => false, 'message' => 'Please login first.' ] );

    $job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
    if ( ! $job_id || get_post_type( $job_id ) !== 'job_listing' ) wp_send_json( [ 'success' => false, 'message' => 'Invalid job.' ] );

    $user_id = get_current_user_id();

    // 1. UPDATE EMPLOYER'S JOB META
    $applications = get_post_meta( $job_id, 'nk_job_applications', true );
    $applications = is_array( $applications ) ? $applications : [];
    
    if ( ! in_array( $user_id, $applications ) ) {
        $applications[] = $user_id;
        update_post_meta( $job_id, 'nk_job_applications', $applications );
    }

    // 2. UPDATE CANDIDATE'S USER META
    $applied_jobs = get_user_meta( $user_id, 'nk_applied_jobs', true );
    $applied_jobs = is_array( $applied_jobs ) ? $applied_jobs : [];

    if ( ! in_array( $job_id, $applied_jobs ) ) {
        $applied_jobs[] = $job_id;
        update_user_meta( $user_id, 'nk_applied_jobs', $applied_jobs );
    }

    wp_send_json( [ 'success' => true, 'message' => 'Application submitted successfully.' ] );
}
add_action( 'wp_ajax_nk_easy_apply', 'nk_easy_apply_ajax' );