<?php
namespace NK_AI_RankMath\Admin;

class Assets {
    public function enqueue_admin($hook) {
        // Only load on relevant pages
        $valid_hooks = ['post.php', 'post-new.php', 'edit.php', 'settings_page_nk-ai-rankmath'];
        if (!in_array($hook, $valid_hooks)) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style(
            'nk-ai-rankmath-admin',
            NK_AI_RANKMATH_URL . 'assets/css/admin.css',
            [],
            NK_AI_RANKMATH_VERSION
        );
        
        // Enqueue scripts
        wp_enqueue_script(
            'nk-ai-rankmath-field-buttons',
            NK_AI_RANKMATH_URL . 'assets/js/field-buttons.js',
            ['jquery', 'wp-api-fetch'],
            NK_AI_RANKMATH_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('nk-ai-rankmath-field-buttons', 'nk_ai_rankmath', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nk_ai_rankmath_nonce'),
            'rest_url' => rest_url('nk-ai-rankmath/v1/'),
            'version' => NK_AI_RANKMATH_VERSION,
        ]);
    }
}