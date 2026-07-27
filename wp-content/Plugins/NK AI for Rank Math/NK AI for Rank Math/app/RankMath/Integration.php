<?php
namespace NK_AI_RankMath\RankMath;

class Integration {
    private $field_detector;
    private $renderer;
    
    public function __construct() {
        $this->field_detector = new Field_Detector();
        $this->renderer = new Renderer();
    }
    
    public function init() {
        // Check if Rank Math is active
        if (!defined('RANK_MATH_VERSION')) {
            return;
        }
        
        // Add filters for Rank Math fields
        add_filter('rank_math/admin/post_columns', [$this, 'add_custom_columns']);
        add_filter('rank_math/admin/metabox_tabs', [$this, 'add_ai_tab']);
        
        // Add action for AI buttons
        add_action('rank_math/admin/metabox/section', [$this, 'render_ai_buttons']);
        
        // Hook into Rank Math settings
        add_filter('rank_math/settings/general', [$this, 'add_ai_settings']);
    }
    
    public function add_ai_buttons() {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        // Determine current screen
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->base, ['post', 'edit'])) {
            return;
        }
        
        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }
        
        $this->renderer->render_buttons($post_id);
    }
    
    public function enqueue_scripts($scripts) {
        $scripts['nk-ai-rankmath'] = [
            'src' => NK_AI_RANKMATH_URL . 'assets/js/field-buttons.js',
            'deps' => ['jquery', 'wp-api-fetch'],
            'version' => NK_AI_RANKMATH_VERSION,
            'in_footer' => true,
        ];
        
        return $scripts;
    }
    
    public function render_ai_buttons($section) {
        // Render AI buttons for specific sections
        if (in_array($section, ['seo', 'social', 'advanced'])) {
            $this->renderer->render_section_buttons($section);
        }
    }
    
    public function add_custom_columns($columns) {
        // Add custom columns for AI features
        $columns['nk_ai_status'] = __('AI Status', 'nk-ai-rankmath');
        return $columns;
    }
    
    public function add_ai_tab($tabs) {
        // Add AI tab to Rank Math metabox
        $tabs['nk_ai'] = [
            'title' => __('NK AI', 'nk-ai-rankmath'),
            'icon' => 'dashicons-robot',
            'priority' => 50,
        ];
        return $tabs;
    }
    
    public function add_ai_settings($settings) {
        // Add AI settings to Rank Math
        $settings['nk_ai_api'] = [
            'title' => __('NK AI API', 'nk-ai-rankmath'),
            'description' => __('Configure NK AI integration', 'nk-ai-rankmath'),
            'fields' => [
                'enabled' => [
                    'type' => 'toggle',
                    'label' => __('Enable NK AI', 'nk-ai-rankmath'),
                ],
            ],
        ];
        return $settings;
    }
}