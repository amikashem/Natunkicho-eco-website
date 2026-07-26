<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * AI JOB DESCRIPTION GENERATOR WITH FALLBACK (PHASE 4)
 * Path: natunkicho-child/inc/ai/ai-job-generator.php
 * =========================================================================
 */
add_action('wp_ajax_nk_generate_ai_job_desc', 'nk_generate_ai_job_desc');

function nk_generate_ai_job_desc() {
    check_ajax_referer('nk_job_submit_nonce', 'security');
    
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error('Please log in to use this feature.');
    }

    // 1. Backend Premium Security Check
    $is_premium = false;
    if ( function_exists('nk_is_user_premium') && nk_is_user_premium($user_id) ) {
        $is_premium = true;
    } elseif ( current_user_can('administrator') ) {
        $is_premium = true;
    }
    if ( ! $is_premium ) {
        wp_send_json_error("Unauthorized: Premium access required for AI features.");
    }

    // 2. Validate Job Title
    $job_title = sanitize_text_field($_POST['job_title']);
    if (empty($job_title)) {
        wp_send_json_error('Please fill out the Job Title field first.');
    }

    // 3. Identify WHICH field requested the AI
    $target_field = isset($_POST['target_field']) ? sanitize_text_field($_POST['target_field']) : '';

    // 4. Map the field to our Prompt Library Keys
    $prompt_key = 'default_desc';
    if ( $target_field === 'job_summary' ) {
        $prompt_key = 'job_summary';
    } elseif ( $target_field === 'job_responsibilities' || $target_field === 'job_description' ) {
        $prompt_key = 'job_responsibilities';
    } elseif ( $target_field === 'job_requirements' ) {
        $prompt_key = 'job_requirements';
    } elseif ( $target_field === 'job_skills' ) {
        $prompt_key = 'job_skills';
    }

    // 5. Prepare user data
    $user_prompt = "Job Title: " . $job_title;

    // 6. Verify Gateway exists
    if ( ! class_exists('NK_AI_Gateway') ) {
        wp_send_json_error('AI Gateway is not active. Please contact support.');
    }

    // 7. Ping the Gateway!
    $response = NK_AI_Gateway::run( 'job_module', $prompt_key, $user_prompt );

    // 8. Send Response back to frontend JavaScript
    if ( $response['success'] ) {
        wp_send_json_success( trim( $response['data'] ) );
    } else {
        wp_send_json_error( 'AI Gateway Error: ' . $response['error'] );
    }
}