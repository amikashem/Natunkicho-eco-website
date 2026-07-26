<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Single source of truth for table names. Every other class calls
 * NK_Database::table('subscribers') instead of hardcoding $wpdb->prefix . 'nk_subscribers'
 * so a prefix change or table rename only happens in one place.
 */
class NK_Database {

    public static function table( $short_name ) {
        global $wpdb;
        $map = array(
            'subscribers'       => 'nk_subscribers',
            'email_queue'       => 'nk_email_queue',
            'email_logs'        => 'nk_email_logs',
            'email_templates'   => 'nk_email_templates',
            'provider_settings' => 'nk_provider_settings',
            'suppression_list'  => 'nk_suppression_list',
        );

        if ( ! isset( $map[ $short_name ] ) ) {
            return false;
        }

        return $wpdb->prefix . $map[ $short_name ];
    }

    /** Simple symmetric encrypt/decrypt for provider API keys at rest, using WP salts. */
    public static function encrypt( $plaintext ) {
        if ( '' === $plaintext || null === $plaintext ) {
            return '';
        }
        $key    = self::encryption_key();
        $iv     = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
        $cipher = openssl_encrypt( $plaintext, 'aes-256-cbc', $key, 0, $iv );
        return base64_encode( $iv . $cipher );
    }

    public static function decrypt( $encoded ) {
        if ( '' === $encoded || null === $encoded ) {
            return '';
        }
        $key        = self::encryption_key();
        $raw        = base64_decode( $encoded );
        $iv_len     = openssl_cipher_iv_length( 'aes-256-cbc' );
        $iv         = substr( $raw, 0, $iv_len );
        $cipher     = substr( $raw, $iv_len );
        $plaintext  = openssl_decrypt( $cipher, 'aes-256-cbc', $key, 0, $iv );
        return false === $plaintext ? '' : $plaintext;
    }

    private static function encryption_key() {
        // Derive a stable 32-byte key from WP's AUTH_KEY salt (defined in wp-config.php).
        $secret = defined( 'AUTH_KEY' ) && AUTH_KEY ? AUTH_KEY : 'nk-email-engine-fallback-key';
        return hash( 'sha256', $secret, true );
    }
}
