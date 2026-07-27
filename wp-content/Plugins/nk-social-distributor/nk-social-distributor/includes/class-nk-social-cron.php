<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Social_Cron {

    public function __construct() {
        // Register the background hook
        add_action( 'nk_social_process_queue_hook', array( $this, 'process_queue' ) );
        
        // Also allow manual triggering via admin-post for immediate testing
        add_action( 'admin_post_nk_force_process_queue', array( $this, 'manual_trigger' ) );
    }

    public function manual_trigger() {
        if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
        $this->process_queue();
        wp_redirect( admin_url( 'admin.php?page=nk-social-distributor&tab=queue&processed=true' ) );
        exit;
    }

    public function process_queue() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nk_social_queue';

        // FIX: Use WordPress local time instead of MySQL server time to avoid Timezone bugs
        $current_wp_time = current_time('mysql');

        // 1. Grab up to 5 pending items that are ready to go
        $items = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE status = 'pending' AND scheduled_time <= %s LIMIT 5",
            $current_wp_time
        ) );

        if ( empty( $items ) ) return;

        foreach ( $items as $item ) {
            // 2. Get the AI Caption
            $message = NK_Social_AI::generate_caption( $item->content_id, $item->platform );
            
            // 3. Send to specific platform safely
            $result = false;
            
            if ( $item->platform === 'telegram' && class_exists('NK_Telegram') ) {
                $telegram = new NK_Telegram();
                $result = $telegram->publish( $item->content_id, $message );
            } elseif ( $item->platform === 'linkedin' && class_exists('NK_LinkedIn') ) {
                $linkedin = new NK_LinkedIn();
                $result = $linkedin->publish( $item->content_id, $message );
            } else {
                $result = new WP_Error( 'missing_module', 'Platform module not active.' );
            }

            // 4. Update the Database Status
            if ( is_wp_error( $result ) ) {
                // FAILED
                $wpdb->update( 
                    $table_name, 
                    array( 'status' => 'failed', 'error_message' => $result->get_error_message() ), 
                    array( 'id' => $item->id ) 
                );
            } else {
                // SUCCESS
                $wpdb->update( 
                    $table_name, 
                    array( 'status' => 'published', 'published_time' => current_time('mysql'), 'external_post_id' => $result, 'error_message' => '' ), 
                    array( 'id' => $item->id ) 
                );
            }
        }
    }
}

new NK_Social_Cron();