<?php
/**
 * NatunKicho AI CV Studio - AI Skill Suggestions
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_nk_generate_ai_skills', 'nk_ajax_generate_ai_skills' );
function nk_ajax_generate_ai_skills() {
    check_ajax_referer( 'nk_cv_builder_nonce', 'security' );
    if ( ! is_user_logged_in() ) wp_send_json_error( 'Please login.' );

    $api_key = defined('NKJP_OPENAI') ? NKJP_OPENAI : (defined('nkjp_openai_key') ? nkjp_openai_key : '');
    if ( empty( $api_key ) ) wp_send_json_error( 'AI configuration missing.' );

    $role = sanitize_text_field( $_POST['target_role'] );

    $prompt = "List the top 10 most in-demand skills for a '{$role}' in the hospitality industry. Provide ONLY a comma-separated list of skills, nothing else. No bullet points, no intro text.";

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
        // Return as an array of trimmed skills
        $skills_string = trim($data['choices'][0]['message']['content']);
        $skills_array = array_map('trim', explode(',', $skills_string));
        wp_send_json_success( $skills_array );
    } else {
        wp_send_json_error( 'AI returned an unexpected response.' );
    }
}