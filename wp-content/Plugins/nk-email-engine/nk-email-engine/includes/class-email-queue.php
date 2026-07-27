<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 10X UPGRADED: Single insertion point for outgoing email. 
 * Fully linked to Dashboard UI for Kill Switch and Throttle limits.
 */
class NK_Email_Queue {

    public static function enqueue( $to_email, $to_name, $subject, $body, array $args = array() ) {
        global $wpdb;

        $to_email = sanitize_email( $to_email );
        if ( ! is_email( $to_email ) ) return false;

        // 🔴 10X UPGRADE: MASTER KILL SWITCH CHECK
        $engine_status = get_option('nk_email_engine_status', 'active');
        if ( $engine_status === 'inactive' ) {
            // Bypass the custom queue entirely. Send immediately via native WP Mail.
            add_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
            $result = wp_mail( $to_email, $subject, $body );
            remove_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
            return $result;
        }

        // Suppression check happens here to keep queue clean
        if ( class_exists('NK_Subscriber_Manager') && NK_Subscriber_Manager::is_suppressed( $to_email ) ) {
            return false;
        }

        // Handle dynamic tags
        if ( ! empty( $args['template_vars'] ) && class_exists('NK_Template_Manager') ) {
            $vars = $args['template_vars'];
            $vars['unsubscribe_link'] = self::build_unsubscribe_link( $to_email );
            $subject = NK_Template_Manager::render( $subject, $vars );
            $body    = NK_Template_Manager::render( $body, $vars );
        }

        $table = NK_Database::table( 'email_queue' );

        $wpdb->insert( $table, array(
            'recipient_email' => $to_email,
            'recipient_name'  => sanitize_text_field( $to_name ),
            'subject'         => sanitize_text_field( $subject ),
            'body'            => wp_kses_post( $body ),
            'provider'        => isset( $args['provider'] ) ? sanitize_text_field( $args['provider'] ) : null,
            'priority'        => isset( $args['priority'] ) ? sanitize_text_field( $args['priority'] ) : 'normal',
            'status'          => 'pending',
            'retry_count'     => 0,
            'scheduled_at'    => isset( $args['scheduled_at'] ) ? $args['scheduled_at'] : current_time( 'mysql' ),
            'created_at'      => current_time( 'mysql' ),
        ) );

        return $wpdb->insert_id;
    }

    public static function enqueue_bulk( array $recipients, $subject, $body, array $args = array() ) {
        $ids = array();
        foreach ( $recipients as $r ) {
            $id = self::enqueue( $r['email'], isset( $r['name'] ) ? $r['name'] : '', $subject, $body, $args );
            if ( $id ) $ids[] = $id;
        }
        return $ids;
    }

    /** * 🔴 10X UPGRADE: Pulls dynamic BATCH_SIZE from Admin UI instead of hardcoding 
     */
    public static function get_next_batch() {
        global $wpdb;
        $table = NK_Database::table( 'email_queue' );
        
        $batch_size = (int) get_option('nk_email_batch_size', 50);
        if ($batch_size <= 0) $batch_size = 50;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE status = 'pending' AND scheduled_at <= %s
             ORDER BY FIELD(priority, 'high', 'normal', 'low'), created_at ASC
             LIMIT %d",
            current_time( 'mysql' ),
            $batch_size
        ), ARRAY_A );
    }

    public static function mark_processing( $id ) {
        global $wpdb;
        $table = NK_Database::table( 'email_queue' );
        return $wpdb->update( $table, array( 'status' => 'processing' ), array( 'id' => $id, 'status' => 'pending' ) );
    }

    public static function mark_sent( $id ) {
        global $wpdb;
        $table = NK_Database::table( 'email_queue' );
        return $wpdb->update( $table, array( 'status' => 'sent', 'sent_at' => current_time( 'mysql' ) ), array( 'id' => $id ) );
    }

    public static function mark_failed( $id, $retry_count ) {
        global $wpdb;
        $table = NK_Database::table( 'email_queue' );
        $status = $retry_count >= 3 ? 'failed' : 'pending';
        return $wpdb->update( $table, array( 'status' => $status, 'retry_count' => $retry_count ), array( 'id' => $id ) );
    }

    public static function get_queue_counts() {
        global $wpdb;
        $table = NK_Database::table( 'email_queue' );
        $rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A );
        $counts = array();
        foreach ( $rows as $r ) { $counts[ $r['status'] ] = (int) $r['total']; }
        return $counts;
    }

    public static function get_recent( $limit = 50 ) {
        global $wpdb;
        $table = NK_Database::table( 'email_queue' );
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d", $limit ), ARRAY_A );
    }

    private static function build_unsubscribe_link( $email ) {
        global $wpdb;
        $table = NK_Database::table( 'subscribers' );
        $token = $wpdb->get_var( $wpdb->prepare( "SELECT unsubscribe_token FROM {$table} WHERE email = %s", $email ) );
        if ( ! $token ) return '';
        return add_query_arg( array( 'nk_unsubscribe' => $token ), home_url( '/' ) );
    }
}