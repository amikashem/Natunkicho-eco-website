<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Email_Logger {

    public static function log( $queue_id, $recipient_email, $provider, $status, $extra = array() ) {
        global $wpdb;
        $table = NK_Database::table( 'email_logs' );

        $wpdb->insert( $table, array(
            'email_queue_id'  => $queue_id,
            'recipient_email' => $recipient_email,
            'provider'        => $provider,
            'status'          => $status,
            'opened'          => isset( $extra['opened'] ) ? (int) $extra['opened'] : 0,
            'clicked'         => isset( $extra['clicked'] ) ? (int) $extra['clicked'] : 0,
            'bounced'         => isset( $extra['bounced'] ) ? (int) $extra['bounced'] : 0,
            'complaint'       => isset( $extra['complaint'] ) ? (int) $extra['complaint'] : 0,
            'created_at'      => current_time( 'mysql' ),
        ) );

        return $wpdb->insert_id;
    }

    public static function mark_event( $email_queue_id, $event ) {
        global $wpdb;
        $table = NK_Database::table( 'email_logs' );

        $allowed = array( 'opened', 'clicked', 'bounced', 'complaint' );
        if ( ! in_array( $event, $allowed, true ) ) {
            return false;
        }

        return $wpdb->update( $table, array( $event => 1 ), array( 'email_queue_id' => $email_queue_id ) );
    }

    public static function get_summary( $days = 30 ) {
        global $wpdb;
        $table = NK_Database::table( 'email_logs' );

        $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                COUNT(*) AS total_sent,
                SUM(opened) AS total_opened,
                SUM(clicked) AS total_clicked,
                SUM(bounced) AS total_bounced,
                SUM(complaint) AS total_complaints
             FROM {$table}
             WHERE created_at >= %s",
            $since
        ), ARRAY_A );

        return $row ? $row : array(
            'total_sent' => 0, 'total_opened' => 0, 'total_clicked' => 0,
            'total_bounced' => 0, 'total_complaints' => 0,
        );
    }

    public static function get_recent( $limit = 50 ) {
        global $wpdb;
        $table = NK_Database::table( 'email_logs' );
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d", $limit ), ARRAY_A );
    }
}
