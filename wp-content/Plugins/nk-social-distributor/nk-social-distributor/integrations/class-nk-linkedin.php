<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_LinkedIn implements NK_Social_Platform {

    public function publish( $content_id, $message, $image_url = '' ) {
        $options = get_option( 'nk_social_settings' );
        $token = isset($options['linkedin_token']) ? trim($options['linkedin_token']) : '';
        $raw_urn = isset($options['linkedin_org_urn']) ? trim($options['linkedin_org_urn']) : '';

        if ( empty( $token ) || empty( $raw_urn ) ) {
            return new WP_Error( 'missing_api', 'LinkedIn Access Token or Organization URN is missing.' );
        }

        // ==========================================
        // SMART URN FORMATTING
        // ==========================================
        $clean_urn = preg_replace('/\s+/', '', $raw_urn);
        $clean_urn = str_replace(array('"', "'"), '', $clean_urn);

        // Auto-format company IDs
        if ( preg_match('/^[0-9]+$/', $clean_urn) ) {
            $clean_urn = 'urn:li:organization:' . $clean_urn;
        } 

        // LinkedIn's Modern POSTS API Endpoint
        $api_url = 'https://api.linkedin.com/rest/posts';

        // Prepare the payload
        $body = array(
            'author'         => $clean_urn,
            'commentary'     => $message,
            'visibility'     => 'PUBLIC',
            'distribution'   => array(
                'feedDistribution'               => 'MAIN_FEED',
                'targetEntities'                 => array(),
                'thirdPartyDistributionChannels' => array()
            ),
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false
        );

        // ==========================================
        // DYNAMIC API VERSIONING (Future-Proof)
        // ==========================================
        // LinkedIn requires 'YYYYMM'. We go back 2 months to ensure we hit a stable release.
        $linkedin_version = date('Ym', strtotime('-2 months'));

        // Prepare the Headers
        $args = array(
            'headers' => array(
                'Authorization'             => 'Bearer ' . $token,
                'X-Restli-Protocol-Version' => '2.0.0',
                'LinkedIn-Version'          => $linkedin_version, 
                'Content-Type'              => 'application/json',
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 15,
        );

        // Send request to LinkedIn
        $response = wp_remote_post( $api_url, $args );

        // Handle WordPress server errors
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body );

        // Handle LinkedIn errors cleanly
        if ( $response_code >= 400 ) {
            $error_msg = isset( $data->message ) ? $data->message : 'Unknown Error';
            return new WP_Error( 'api_error', "LinkedIn Error: {$error_msg} (Version Used: {$linkedin_version})" );
        }

        // Success! The modern API returns the ID in a header, not the body.
        $headers = wp_remote_retrieve_headers( $response );
        $post_urn = isset( $headers['x-restli-id'] ) ? $headers['x-restli-id'] : 'published_successfully';

        return $post_urn;
    }
}