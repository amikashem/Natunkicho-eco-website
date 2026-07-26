<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1. SAVE JOB BUTTON SHORTCODE
 * Shortcode: [nk_save_job id="123"]
 */
function nk_save_job_button( $atts ) {
    $atts = shortcode_atts( [ 'id' => get_the_ID() ], $atts );
    $job_id = intval( $atts['id'] );

    if ( ! is_user_logged_in() ) {
        return '<a href="/login/" class="nk-save-job-btn login-to-save">♡ Save Job</a>';
    }

    $user_id = get_current_user_id();
    $saved_jobs = get_user_meta( $user_id, 'nk_saved_jobs', true );
    $saved_jobs = is_array( $saved_jobs ) ? $saved_jobs : [];
    
    // Check if job is already saved
    $is_saved = in_array( $job_id, $saved_jobs );
    $button_text = $is_saved ? '✓ Saved' : '♡ Save Job';
    $button_class = $is_saved ? 'nk-save-job-btn saved' : 'nk-save-job-btn';
    
    // Generate Security Token
    $nonce = wp_create_nonce( 'nk_save_job_nonce' );

    ob_start();
    ?>
    <button class="<?php echo esc_attr( $button_class ); ?>" data-job="<?php echo esc_attr( $job_id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
        <?php echo esc_html( $button_text ); ?>
    </button>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_save_job', 'nk_save_job_button' );

/**
 * 2. AJAX HANDLER (Toggle Save/Unsave)
 */
function nk_save_job_ajax() {
    // Verify Security Token
    check_ajax_referer( 'nk_save_job_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json( [ 'success' => false, 'message' => 'Please login first.' ] );
    }

    $job_id = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
    $user_id = get_current_user_id();

    $saved_jobs = get_user_meta( $user_id, 'nk_saved_jobs', true );
    $saved_jobs = is_array( $saved_jobs ) ? $saved_jobs : [];

    if ( in_array( $job_id, $saved_jobs ) ) {
        // UNSAVE: Remove from array
        $saved_jobs = array_diff( $saved_jobs, [ $job_id ] );
        update_user_meta( $user_id, 'nk_saved_jobs', $saved_jobs );
        wp_send_json( [ 'success' => true, 'status' => 'unsaved', 'message' => 'Job removed.' ] );
    } else {
        // SAVE: Add to array
        $saved_jobs[] = $job_id;
        update_user_meta( $user_id, 'nk_saved_jobs', $saved_jobs );
        wp_send_json( [ 'success' => true, 'status' => 'saved', 'message' => 'Job saved.' ] );
    }
}
add_action( 'wp_ajax_nk_save_job', 'nk_save_job_ajax' );
add_action( 'wp_ajax_nopriv_nk_save_job', 'nk_save_job_ajax' );