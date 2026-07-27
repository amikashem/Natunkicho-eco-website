<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_AI_Gemini {

    private $api_key;
    private $model;

    public function __construct( $api_key, $model = 'gemini-2.5-flash' ) { // Changed default to gemini-2.5-flash
        $this->api_key = $api_key;
        $this->model   = $model;
    }

    /**
     * Executes the API call to Google Gemini
     */
    public function generate( $system_prompt, $user_prompt, $temperature = 0.7 ) {
        // Use the model from constructor parameter
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->api_key;

        // Gemini payload structure
        $body = array(
            'system_instruction' => array(
                'parts' => array( array( 'text' => $system_prompt ) )
            ),
            'contents' => array(
                array(
                    'role'  => 'user',
                    'parts' => array( array( 'text' => $user_prompt ) )
                )
            ),
            'generationConfig' => array(
                'temperature' => (float) $temperature
            )
        );

        $args = array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 30
        );

        $response = wp_remote_post( $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'error' => $response->get_error_message() );
        }

        $body_decoded = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body_decoded['error'] ) ) {
            return array( 'success' => false, 'error' => $body_decoded['error']['message'] );
        }

        // Extract usage tokens
        $tokens_used = isset( $body_decoded['usageMetadata']['totalTokenCount'] ) ? $body_decoded['usageMetadata']['totalTokenCount'] : 0;
        $ai_content  = $body_decoded['candidates'][0]['content']['parts'][0]['text'];

        return array(
            'success'      => true,
            'data'         => $ai_content,
            'tokens_used'  => $tokens_used
        );
    }
}