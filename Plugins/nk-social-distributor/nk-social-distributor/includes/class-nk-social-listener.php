<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Social_Listener {

    public function __construct() {
        // transition_post_status is the safest WordPress hook for this.
        // It catches manual publishes, scheduled crons, and WP Job Manager frontend submissions.
        add_action( 'transition_post_status', array( $this, 'detect_new_publish' ), 10, 3 );
    }

    public function detect_new_publish( $new_status, $old_status, $post ) {
        // 1. We ONLY care if it is transitioning to "publish" for the very first time.
        if ( 'publish' !== $new_status || 'publish' === $old_status ) {
            return;
        }

        // 2. We ONLY care about specific content types.
        $allowed_types = array( 'job_listing', 'post', 'course' );
        if ( ! in_array( $post->post_type, $allowed_types ) ) {
            return;
        }

        // 3. Check Admin Settings to see which platforms are enabled.
        $options = get_option( 'nk_social_settings' );
        $telegram_enabled = isset( $options['telegram_enabled'] ) ? $options['telegram_enabled'] : 0;
        $linkedin_enabled = isset( $options['linkedin_enabled'] ) ? $options['linkedin_enabled'] : 0;

        // 4. Send to Queue!
        if ( $telegram_enabled ) {
            NK_Social_Queue::add_to_queue( $post->ID, $post->post_type, 'telegram' );
        }
        
        if ( $linkedin_enabled ) {
            NK_Social_Queue::add_to_queue( $post->ID, $post->post_type, 'linkedin' );
        }
    }
}

new NK_Social_Listener();