<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_AI_OpenAI {

    private $api_key;
    private $model;

    public function __construct( $api_key, $model = 'gpt-3.5-turbo' ) {
        $this->api_key = $api_key;
        $this->model   = $model;
    }

    /**
     * Executes the API call to OpenAI
     */
    public function generate( $system_prompt, $user_prompt, $temperature = 0.7 ) {
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $body = array(
            'model'       => $this->model,
            'temperature' => (float) $temperature,
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role'    => 'user',
                    'content' => $user_prompt
                )
            )
        );

        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json'
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 30 // 30 seconds for AI processing
        );

        $response = wp_remote_post( $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'error' => $response->get_error_message() );
        }

        $body_decoded = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body_decoded['error'] ) ) {
            return array( 'success' => false, 'error' => $body_decoded['error']['message'] );
        }

        // Extract usage tokens for cost tracking
        $tokens_used = isset( $body_decoded['usage']['total_tokens'] ) ? $body_decoded['usage']['total_tokens'] : 0;
        $ai_content  = $body_decoded['choices'][0]['message']['content'];

        return array(
            'success'      => true,
            'data'         => $ai_content,
            'tokens_used'  => $tokens_used
        );
    }
}