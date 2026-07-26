<?php
namespace NK_AI_RankMath\Core;

class Activator {
    public static function activate() {
        // Set default options
        $defaults = [
            'enabled' => 1,
            'auto_suggest' => 1,
            'features_seo_title' => 1,
            'features_meta_description' => 1,
            'features_focus_keyword' => 1,
            'features_keyword_suggestions' => 1,
            'features_schema_generation' => 1,
            'features_faq_generation' => 1,
            'features_internal_links' => 1,
            'features_image_alt' => 1,
            'features_readability_improvement' => 1,
            'features_content_optimization' => 1,
            'features_seo_score_improvement' => 1,
            'features_bulk_optimization' => 1,
        ];
        
        if (!get_option('nk_ai_rankmath_settings')) {
            add_option('nk_ai_rankmath_settings', $defaults);
        }
        
        // Clear cache
        \NK_AI_RankMath\Helpers\Cache::get_instance()->clear();
    }
}