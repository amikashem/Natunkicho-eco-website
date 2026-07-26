<?php
namespace NK_AI_RankMath\AI\Handlers;

use NK_AI_RankMath\AI\Gateway;

class SEO_Title_Handler {
    public static function generate($post_id, $content) {
        $context = self::prepare_context($post_id);
        
        $response = Gateway::run(
            'seo_title',
            'seo_title',
            $content,
            $context
        );
        
        if ($response['success']) {
            return $response['result'];
        }
        
        return false;
    }
    
    private static function prepare_context($post_id) {
        $post = get_post($post_id);
        $current_title = get_post_meta($post_id, 'rank_math_title', true);
        $keyword = get_post_meta($post_id, 'rank_math_focus_keyword', true);
        
        return [
            'keyword' => $keyword,
            'current_title' => $current_title ?: $post->post_title,
            'length' => 60,
            'min_length' => 40,
            'max_length' => 60,
            'tone' => apply_filters('nk_ai_seo_title_tone', 'professional'),
            'post_type' => $post->post_type,
            'post_id' => $post_id,
            'language' => get_locale(),
            'site_name' => get_bloginfo('name'),
        ];
    }
}