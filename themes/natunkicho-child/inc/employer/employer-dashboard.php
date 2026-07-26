<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================
 * EMPLOYER DASHBOARD UTILITIES
 * =========================================
 */

/**
 * Get total published jobs for an employer
 */
function nk_get_employer_active_job_count( $user_id ) {
    $args = [
        'post_type'   => 'job_listing',
        'post_status' => 'publish',
        'author'      => $user_id,
        'fields'      => 'ids',
    ];
    $query = new WP_Query( $args );
    return $query->found_posts;
}