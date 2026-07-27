<?php
/**
 * NatunKicho AI CV Studio - AI Summary Generator
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_nk_generate_ai_summary', 'nk_ajax_generate_ai_summary' );
function nk_ajax_generate_ai_summary() {
    check_ajax_referer( 'nk_cv_builder_nonce', 'security' );
    if ( ! is_user_logged_in() ) wp_send_json_error( 'Please login.' );

    $api_key = defined('NKJP_OPENAI') ? NKJP_OPENAI : (defined('nkjp_openai_key') ? nkjp_openai_key : '');
    if ( empty( $api_key ) ) wp_send_json_error( 'AI configuration missing.' );

    $role = sanitize_text_field( $_POST['target_role'] );
    $exp  = sanitize_textarea_field( $_POST['experience'] );

    $prompt = "You are an expert resume writer for the hospitality industry. Write a highly professional, ATS-friendly CV summary (max 3-4 sentences) for a '{$role}'. Use this experience as context if provided: '{$exp}'. DO NOT include any introductory text, quotes, or formatting. Just return the summary paragraph directly.";

    $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
        'headers' => [ 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ],
        'body'    => wp_json_encode([
            'model'    => 'openai/gpt-3.5-turbo',
            'messages' => [ [ 'role' => 'user', 'content' => $prompt ] ]
        ]),
        'timeout' => 45
    ]);

    if ( is_wp_error( $response ) ) wp_send_json_error( 'Failed to connect to AI.' );
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( isset( $data['choices'][0]['message']['content'] ) ) {
        wp_send_json_success( trim($data['choices'][0]['message']['content']) );
    } else {
        wp_send_json_error( 'AI returned an unexpected response.' );
    }
}