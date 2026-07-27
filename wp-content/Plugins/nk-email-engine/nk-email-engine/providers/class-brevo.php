<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Brevo (formerly Sendinblue) transactional email provider.
 * Docs: https://developers.brevo.com/reference/sendtransacemail
 */
class NK_Provider_Brevo implements NK_Email_Provider_Interface {

    private $api_key;
    private $from_email;
    private $from_name;

    const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct() {
        $settings        = self::get_settings();
        $this->api_key   = $settings['api_key'];
        $this->from_email = apply_filters( 'nk_email_from_address', get_option( 'admin_email' ) );
        $this->from_name  = apply_filters( 'nk_email_from_name', get_bloginfo( 'name' ) );
    }

    public function get_name() {
        return 'brevo';
    }

    public function is_configured() {
        return ! empty( $this->api_key );
    }

    public static function get_settings() {
        global $wpdb;
        $table = NK_Database::table( 'provider_settings' );
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE provider_name = %s", 'brevo' ), ARRAY_A );

        if ( ! $row ) {
            return array( 'api_key' => '' );
        }

        return array( 'api_key' => NK_Database::decrypt( $row['api_key'] ) );
    }

    public function send( $to_email, $to_name, $subject, $html_body ) {
        if ( ! $this->is_configured() ) {
            return array( 'success' => false, 'message_id' => '', 'error' => 'Brevo is not configured.' );
        }

        $payload = array(
            'sender'      => array( 'name' => $this->from_name, 'email' => $this->from_email ),
            'to'          => array( array( 'email' => $to_email, 'name' => $to_name ? $to_name : $to_email ) ),
            'subject'     => $subject,
            'htmlContent' => $html_body,
        );

        $response = wp_remote_post( self::ENDPOINT, array(
            'headers' => array(
                'accept'       => 'application/json',
                'api-key'      => $this->api_key,
                'content-type' => 'application/json',
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message_id' => '', 'error' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( in_array( $code, array( 200, 201 ), true ) && ! empty( $data['messageId'] ) ) {
            return array( 'success' => true, 'message_id' => $data['messageId'], 'error' => '' );
        }

        $error = isset( $data['message'] ) ? $data['message'] : 'Unknown Brevo error (HTTP ' . $code . ')';
        return array( 'success' => false, 'message_id' => '', 'error' => $error );
    }
}
