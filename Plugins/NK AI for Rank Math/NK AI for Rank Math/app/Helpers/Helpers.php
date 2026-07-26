<?php
namespace NK_AI_RankMath\Helpers;

class Helpers {
    /**
     * Get current post content
     */
    public static function get_post_content($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }
        
        return $post->post_content;
    }
    
    /**
     * Get post meta for Rank Math
     */
    public static function get_rankmath_meta($post_id, $key) {
        return get_post_meta($post_id, "rank_math_{$key}", true);
    }
    
    /**
     * Update post meta for Rank Math
     */
    public static function update_rankmath_meta($post_id, $key, $value) {
        return update_post_meta($post_id, "rank_math_{$key}", $value);
    }
    
    /**
     * Get supported post types
     */
    public static function get_supported_post_types() {
        $types = ['post', 'page'];
        
        if (class_exists('WooCommerce')) {
            $types[] = 'product';
        }
        
        return apply_filters('nk_ai_supported_post_types', $types);
    }
    
    /**
     * Check if content is empty
     */
    public static function is_content_empty($content) {
        $content = trim(wp_strip_all_tags($content));
        return empty($content);
    }
    
    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $ellipsis = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $ellipsis;
    }
}