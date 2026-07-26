<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Provider_Amazon_SES implements NK_Email_Provider_Interface {

    private $access_key;
    private $secret_key;
    private $region;
    private $from_email;
    private $from_name;

    public function __construct() {
        $settings = self::get_settings();
        $this->access_key = $settings['api_key'];     
        $this->secret_key = $settings['secret_key'];  
        $this->region     = $settings['region'] ? $settings['region'] : 'us-east-1';
        $this->from_email = apply_filters( 'nk_email_from_address', get_option( 'admin_email' ) );
        $this->from_name  = apply_filters( 'nk_email_from_name', get_bloginfo( 'name' ) );
    }

    public function get_name() { return 'amazon_ses'; }

    public function is_configured() {
        return ! empty( $this->access_key ) && ! empty( $this->secret_key ) && ! empty( $this->region );
    }

    // 🔴 10X FIX: Restored the secure AES-256 Decryption connection to the DB
    public static function get_settings() {
        global $wpdb;
        $table = NK_Database::table( 'provider_settings' );
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE provider_name = %s", 'amazon_ses' ), ARRAY_A );

        if ( ! $row ) {
            return array( 'api_key' => '', 'secret_key' => '', 'region' => 'us-east-1' );
        }

        return array(
            'api_key'    => NK_Database::decrypt( $row['api_key'] ),
            'secret_key' => NK_Database::decrypt( $row['secret_key'] ),
            'region'     => $row['region'],
        );
    }

    public function send( $to_email, $to_name, $subject, $html_body ) {
        if ( ! $this->is_configured() ) {
            return array( 'success' => false, 'message_id' => '', 'error' => 'Amazon SES is not configured.' );
        }

        $endpoint = "https://email.{$this->region}.amazonaws.com/v2/email/outbound-emails";

        $payload = array(
            'FromEmailAddress' => sprintf( '%s <%s>', $this->from_name, $this->from_email ),
            'Destination'      => array( 'ToAddresses' => array( $to_name ? sprintf( '%s <%s>', $to_name, $to_email ) : $to_email ) ),
            'Content'          => array(
                'Simple' => array(
                    'Subject' => array( 'Data' => $subject, 'Charset' => 'UTF-8' ),
                    'Body'    => array( 'Html' => array( 'Data' => $html_body, 'Charset' => 'UTF-8' ) ),
                ),
            ),
        );

        $body = wp_json_encode( $payload );
        $headers = $this->sign_request( 'POST', $endpoint, $body );

        $response = wp_remote_post( $endpoint, array( 'headers' => $headers, 'body' => $body, 'timeout' => 15 ) );

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message_id' => '', 'error' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw_body, true );

        if ( 200 === $code && ! empty( $data['MessageId'] ) ) {
            return array( 'success' => true, 'message_id' => $data['MessageId'], 'error' => '' );
        }

        // 🔴 10X FIX: Expose the EXACT raw error from AWS
        $error_msg = 'Unknown Error';
        if ( isset( $data['message'] ) ) {
            $error_msg = $data['message'];
        } elseif ( isset( $data['Message'] ) ) {
            $error_msg = $data['Message'];
        } else {
            // Dump raw output if JSON format is unexpected (This reveals the exact AWS Firewall block!)
            $error_msg = $raw_body; 
        }
        
        return array( 'success' => false, 'message_id' => '', 'error' => 'AWS Error (' . $code . '): ' . $error_msg );
    }

    private function sign_request( $method, $url, $body ) {
        $parsed   = wp_parse_url( $url );
        $host     = $parsed['host'];
        $path     = $parsed['path'];
        $service  = 'ses';
        $amz_date = gmdate( 'Ymd\THis\Z' );
        $date     = gmdate( 'Ymd' );

        $canonical_headers = "content-type:application/json\nhost:{$host}\nx-amz-date:{$amz_date}\n";
        $signed_headers    = 'content-type;host;x-amz-date';
        $payload_hash      = hash( 'sha256', $body );

        $canonical_request = implode( "\n", array( $method, $path, '', $canonical_headers, $signed_headers, $payload_hash ) );
        $credential_scope = "{$date}/{$this->region}/{$service}/aws4_request";
        $string_to_sign   = implode( "\n", array( 'AWS4-HMAC-SHA256', $amz_date, $credential_scope, hash( 'sha256', $canonical_request ) ) );

        $k_date    = hash_hmac( 'sha256', $date, 'AWS4' . $this->secret_key, true );
        $k_region  = hash_hmac( 'sha256', $this->region, $k_date, true );
        $k_service = hash_hmac( 'sha256', $service, $k_region, true );
        $k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
        $signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

        return array(
            'Content-Type'  => 'application/json',
            'X-Amz-Date'    => $amz_date,
            'Authorization' => "AWS4-HMAC-SHA256 Credential={$this->access_key}/{$credential_scope}, SignedHeaders={$signed_headers}, Signature={$signature}",
        );
    }
}