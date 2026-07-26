<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_AI_Queue {

    public static function init() {
        // Hook into a WordPress custom cron schedule
        add_action( 'nk_ai_process_queue_hook', array( __CLASS__, 'process_batch' ) );
        
        // Register the cron event if it doesn't exist
        if ( ! wp_next_scheduled( 'nk_ai_process_queue_hook' ) ) {
            wp_schedule_event( time(), 'nk_ai_every_five_minutes', 'nk_ai_process_queue_hook' );
        }

        // Add the custom 5-minute schedule
        add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );
    }

    public static function add_cron_interval( $schedules ) {
        $schedules['nk_ai_every_five_minutes'] = array(
            'interval' => 300, // 5 minutes in seconds
            'display'  => esc_html__( 'Every 5 Minutes (AI Queue)' ),
        );
        return $schedules;
    }

    /**
     * Add a heavy job to the queue
     */
    public static function add_job( $job_type, $payload_array ) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'nk_ai_queue',
            array(
                'job_type' => sanitize_text_field( $job_type ),
                'payload'  => wp_json_encode( $payload_array ),
                'status'   => 'pending',
                'attempts' => 0
            ),
            array( '%s', '%s', '%s', '%d' )
        );
        return $wpdb->insert_id;
    }

    /**
     * Process 5 jobs at a time (runs automatically in the background)
     */
    public static function process_batch() {
        global $wpdb;
        $table = $wpdb->prefix . 'nk_ai_queue';

        // Get 5 pending jobs
        $jobs = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'pending' LIMIT 5" );

        if ( empty( $jobs ) ) {
            return;
        }

        foreach ( $jobs as $job ) {
            // Mark as processing
            $wpdb->update( $table, array( 'status' => 'processing', 'attempts' => $job->attempts + 1 ), array( 'id' => $job->id ) );
            
            $payload = json_decode( $job->payload, true );
            
            // Execute the Job based on Type
            try {
                if ( $job->job_type === 'bulk_seo' ) {
                    // Example: NK_AI_Gateway::run('seo', 'bulk', $payload['text']);
                } elseif ( $job->job_type === 'resume_parse' ) {
                    // Example: NK_AI_Gateway::run('ats', 'parse', $payload['cv_text']);
                }

                // If successful, mark as completed
                $wpdb->update( $table, array( 'status' => 'completed' ), array( 'id' => $job->id ) );

            } catch ( Exception $e ) {
                // If it fails, check attempts
                $new_status = ( $job->attempts >= 2 ) ? 'failed' : 'pending';
                $wpdb->update( $table, array( 'status' => $new_status ), array( 'id' => $job->id ) );
            }
        }
    }
}