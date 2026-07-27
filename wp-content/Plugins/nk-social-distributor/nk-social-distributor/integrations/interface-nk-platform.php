<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Interface NK_Social_Platform
 * Every social network integration MUST follow this structure.
 */
interface NK_Social_Platform {
    /**
     * Publishes content to the platform.
     * @return string|WP_Error Returns the external Post ID on success, or WP_Error on failure.
     */
    public function publish( $content_id, $message, $image_url = '' );
}