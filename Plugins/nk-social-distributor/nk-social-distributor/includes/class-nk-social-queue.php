<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Social_Queue {

    /**
     * Inserts a new post into the custom queue table.
     */
    public static function add_to_queue( $post_id, $post_type, $platform, $scheduled_time = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nk_social_queue';

        if ( empty( $scheduled_time ) ) {
            // Default to right now if not scheduled
            $scheduled_time = current_time( 'mysql' );
        }

        // Prevent duplicate queueing for the exact same post and platform
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table_name WHERE content_id = %d AND platform = %s AND status IN ('pending', 'scheduled')",
            $post_id,
            $platform
        ) );

        if ( $existing ) {
            return false; // Already in queue
        }

        $wpdb->insert(
            $table_name,
            array(
                'content_id'     => $post_id,
                'content_type'   => $post_type,
                'platform'       => $platform,
                'status'         => 'pending',
                'scheduled_time' => $scheduled_time
            ),
            array( '%d', '%s', '%s', '%s', '%s' )
        );

        return $wpdb->insert_id;
    }
}