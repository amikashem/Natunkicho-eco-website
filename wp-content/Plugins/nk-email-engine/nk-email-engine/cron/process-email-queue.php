<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 10X UPGRADE: DUAL-CRON ARCHITECTURE
 * 1. Registers the native WP-Cron fallback.
 * 2. Exposes a highly secure Server Cron endpoint for cPanel.
 */

// --- 1. NATIVE WP-CRON FALLBACK ---
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => __( 'Every Minute', 'nk-email-engine' ),
    );
    return $schedules;
} );

// Hook into the WP-Cron event
add_action( 'nk_process_email_queue', array( 'NK_Cron_Queue_Processor', 'run' ) );


// --- 2. HIGH-PERFORMANCE SERVER CRON ENDPOINT ---
// URL to ping via cPanel/Server Cron: https://natunkicho.com/?nk_cron_trigger=process_emails_secret_10x
add_action( 'init', function() {
    if ( isset($_GET['nk_cron_trigger']) && $_GET['nk_cron_trigger'] === 'process_emails_secret_10x' ) {
        NK_Cron_Queue_Processor::run(true);
        wp_die('10X Queue Engine Processed Successfully.');
    }
});


/**
 * Core Queue Processor
 */
class NK_Cron_Queue_Processor {

    const LOCK_KEY = 'nk_email_queue_processing_lock';
    const LOCK_TTL = 55; // seconds — just under the 60s cron interval.

    public static function run( $is_server_cron = false ) {
        
        // 🔴 10X UPGRADE: MASTER KILL SWITCH
        // If the admin turned off the engine in the dashboard, do not process the queue.
        if ( get_option('nk_email_engine_status', 'active') === 'inactive' ) {
            return; 
        }

        // Overlap protection: prevent double-sending
        if ( get_transient( self::LOCK_KEY ) ) {
            return; // Previous run still in progress.
        }
        
        set_transient( self::LOCK_KEY, 1, self::LOCK_TTL );

        try {
            self::process_batch();
        } finally {
            delete_transient( self::LOCK_KEY );
        }
    }

    private static function process_batch() {
        
        // Pulls dynamic batch size set by Admin UI
        $batch = NK_Email_Queue::get_next_batch();

        if ( empty( $batch ) ) {
            return;
        }

        $provider = NK_Provider_Manager::get_active_provider();
        
        // Failsafe: Ensure provider exists and has API keys configured
        if ( ! $provider || ! $provider->is_configured() ) {
            error_log('[NK Email Engine] Queue paused: Active Provider (SES/Brevo) is missing API keys.');
            return;
        }

        foreach ( $batch as $email ) {
            // Claim the row first so a concurrent run can't grab it too.
            $claimed = NK_Email_Queue::mark_processing( $email['id'] );
            if ( ! $claimed ) {
                continue;
            }

            // Re-check suppression at send-time
            if ( NK_Subscriber_Manager::is_suppressed( $email['recipient_email'] ) ) {
                NK_Email_Queue::mark_failed( $email['id'], 3 ); // permanent fail
                if(class_exists('NK_Email_Logger')) {
                    NK_Email_Logger::log( $email['id'], $email['recipient_email'], $provider->get_name(), 'suppressed' );
                }
                continue;
            }

            // Fire the email via Amazon SES / Brevo
            $result = $provider->send( $email['recipient_email'], $email['recipient_name'], $email['subject'], $email['body'] );

            if ( $result['success'] ) {
                NK_Email_Queue::mark_sent( $email['id'] );
                if(class_exists('NK_Email_Logger')) {
                    NK_Email_Logger::log( $email['id'], $email['recipient_email'], $provider->get_name(), 'sent' );
                }
            } else {
                $retry_count = (int) $email['retry_count'] + 1;
                NK_Email_Queue::mark_failed( $email['id'], $retry_count );
                
                if(class_exists('NK_Email_Logger')) {
                    // We check if the logger expects 4 or 5 arguments based on your Logger class
                    NK_Email_Logger::log( $email['id'], $email['recipient_email'], $provider->get_name(), 'failed', array('error' => $result['error']) );
                }

                // Surface persistent provider failures to the admin via error log.
                if ( $retry_count >= 3 ) {
                    error_log( sprintf( '[NK Email Engine] Permanent send failure for %s: %s', $email['recipient_email'], $result['error'] ) );
                }
            }
        }
    }
}