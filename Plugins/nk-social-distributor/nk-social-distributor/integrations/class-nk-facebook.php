<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Facebook implements NK_Social_Platform {

    public function publish( $content_id, $message, $image_url = '' ) {
        $options = get_option( 'nk_social_settings' );
        $token = isset($options['facebook_token']) ? trim($options['facebook_token']) : '';
        $page_id = isset($options['facebook_page_id']) ? trim($options['facebook_page_id']) : '';

        if ( empty( $token ) || empty( $page_id ) ) {
            return new WP_Error( 'missing_api', 'Facebook Page Access Token or Page ID is missing.' );
        }

        // Clean Page ID to ensure only numbers are sent
        $page_id = preg_replace('/[^0-9]/', '', $page_id);

        // Facebook Graph API Endpoint (Posts to the Page's Feed)
        $api_url = "https://graph.facebook.com/v19.0/{$page_id}/feed";

        // Prepare the payload
        $body = array(
            'message'      => $message,
            'access_token' => $token
        );

        // Send request to Facebook
        $args = array(
            'body'    => $body,
            'timeout' => 15,
        );

        $response = wp_remote_post( $api_url, $args );

        // Handle WordPress server errors
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body );

        // Handle Facebook specific errors
        if ( $response_code >= 400 ) {
            $error_msg = isset( $data->error->message ) ? $data->error->message : 'Unknown Facebook Error';
            return new WP_Error( 'api_error', "Facebook Error: {$error_msg}" );
        }

        // Success! Return the Facebook Post ID
        return isset($data->id) ? $data->id : 'unknown_id';
    }
}