<?php
if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/inc/api/adzuna.php';
require_once get_stylesheet_directory() . '/inc/api/jsearch.php';
require_once get_stylesheet_directory() . '/inc/api/careerjet.php';
require_once get_stylesheet_directory() . '/inc/api/findwork.php';

/**
 * Fetch Internal WPJM Jobs
 */
function nk_fetch_internal_jobs($keywords = '', $location = '') {
    $jobs = [];
    $args = ['post_type' => 'job_listing', 'posts_per_page' => 12, 'post_status' => 'publish', 's' => $keywords];
    $internal_posts = get_posts($args);
    foreach ($internal_posts as $post) {
        $jobs[] = [
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'company'     => get_post_meta($post->ID, '_company_name', true) ?: 'Internal',
            'location'    => get_post_meta($post->ID, '_job_location', true) ?: 'Location',
            'description' => $post->post_content, // Use post_content for internal jobs
            'url'         => get_permalink($post->ID),
            'source'      => 'Internal'
        ];
    }
    return $jobs;
}
/**
 * Aggregates ALL jobs (Internal + External)
 */
function nk_get_unified_jobs($keywords = '', $location = '') {
    $internal = nk_fetch_internal_jobs($keywords, $location);
    $external = array_merge(
        nk_fetch_adzuna_jobs($keywords, $location),
        nk_fetch_jsearch_jobs($keywords, $location),
        nk_fetch_careerjet_jobs($keywords, $location),
        nk_fetch_findwork_jobs($keywords, $location)
    );
    
    return array_merge($internal, $external);
}