<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Telegram implements NK_Social_Platform {

    public function publish( $content_id, $message, $image_url = '' ) {
        $options = get_option( 'nk_social_settings' );
        $token = isset($options['telegram_bot_token']) ? $options['telegram_bot_token'] : '';
        $chat_id = isset($options['telegram_chat_id']) ? $options['telegram_chat_id'] : '';

        if ( empty( $token ) || empty( $chat_id ) ) {
            return new WP_Error( 'missing_api', 'Telegram Bot Token or Chat ID is missing in settings.' );
        }

        // Telegram API Endpoint
        $api_url = "https://api.telegram.org/bot{$token}/sendMessage";

        // Prepare the payload
        $args = array(
            'body' => array(
                'chat_id'                  => $chat_id,
                'text'                     => $message,
                'parse_mode'               => 'HTML', // Allows us to use bold/links later
                'disable_web_page_preview' => 'false', // Ensures the NatunKicho image/link preview shows
            ),
            'timeout' => 15,
        );

        // Send the request
        $response = wp_remote_post( $api_url, $args );

        // Handle Server Errors
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        // Handle Telegram API Errors (e.g., bad token, bot kicked from channel)
        if ( ! $data->ok ) {
            return new WP_Error( 'api_error', 'Telegram Error: ' . $data->description );
        }

        // Success! Return the Telegram Message ID
        return $data->result->message_id;
    }
}