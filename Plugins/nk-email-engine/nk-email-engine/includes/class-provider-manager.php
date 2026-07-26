<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Provider_Manager {

    public static function get_active_provider() {
        $active = self::get_active_provider_name();

        // 🛑 10X UPGRADE: The Kill Switch
        if ( $active === 'paused' ) {
            return false; // Returns false so the Queue Processor knows to abort sending!
        }

        switch ( $active ) {
            case 'brevo':
                return new NK_Provider_Brevo();
            case 'amazon_ses':
            default:
                return new NK_Provider_Amazon_SES();
        }
    }

    public static function get_active_provider_name() {
        // Check if the Kill Switch is engaged first
        if ( get_option('nk_kill_switch') === 'yes' ) {
            return 'paused';
        }

        global $wpdb;
        $table = NK_Database::table( 'provider_settings' );
        $name  = $wpdb->get_var( "SELECT provider_name FROM {$table} WHERE is_active = 1 LIMIT 1" );
        return $name ? $name : 'amazon_ses';
    }

    public static function switch_provider( $provider_name ) {
        global $wpdb;
        $table = NK_Database::table( 'provider_settings' );

        $allowed = array( 'amazon_ses', 'brevo' );
        if ( ! in_array( $provider_name, $allowed, true ) ) return false;

        $wpdb->query( "UPDATE {$table} SET is_active = 0" );
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE provider_name = %s", $provider_name ) );

        if ( $exists ) {
            $wpdb->update( $table, array( 'is_active' => 1 ), array( 'provider_name' => $provider_name ) );
        } else {
            $wpdb->insert( $table, array( 'provider_name' => $provider_name, 'is_active' => 1, 'created_at' => current_time( 'mysql' ) ) );
        }
        return true;
    }

    public static function save_credentials( $provider_name, $api_key, $secret_key = '', $region = '' ) {
        global $wpdb;
        $table = NK_Database::table( 'provider_settings' );

        $data = array(
            'api_key'    => NK_Database::encrypt( $api_key ),
            'secret_key' => NK_Database::encrypt( $secret_key ),
            'region'     => $region,
        );

        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE provider_name = %s", $provider_name ) );

        if ( $exists ) {
            $wpdb->update( $table, $data, array( 'provider_name' => $provider_name ) );
        } else {
            $data['provider_name'] = $provider_name;
            $data['is_active']     = 0;
            $data['created_at']    = current_time( 'mysql' );
            $wpdb->insert( $table, $data );
        }
        return true;
    }

    public static function get_all_provider_rows() {
        global $wpdb;
        $table = NK_Database::table( 'provider_settings' );
        return $wpdb->get_results( "SELECT id, provider_name, region, is_active, created_at FROM {$table}", ARRAY_A );
    }
}