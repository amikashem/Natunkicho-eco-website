<?php
namespace NK_AI_RankMath\Admin;

class Settings {
    private $option_name = 'nk_ai_rankmath_settings';
    
    public function init() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function add_settings_page() {
        add_options_page(
            __('NK AI for Rank Math', 'nk-ai-rankmath'),
            __('NK AI RankMath', 'nk-ai-rankmath'),
            'manage_options',
            'nk-ai-rankmath',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        register_setting(
            'nk_ai_rankmath_options',
            $this->option_name,
            [$this, 'validate_settings']
        );
        
        // Add settings sections
        add_settings_section(
            'general',
            __('General Settings', 'nk-ai-rankmath'),
            [$this, 'render_general_section'],
            'nk_ai_rankmath_options'
        );
        
        // Add settings fields
        add_settings_field(
            'enabled',
            __('Enable NK AI', 'nk-ai-rankmath'),
            [$this, 'render_toggle_field'],
            'nk_ai_rankmath_options',
            'general',
            ['key' => 'enabled']
        );
        
        add_settings_field(
            'auto_suggest',
            __('Auto Suggestions', 'nk-ai-rankmath'),
            [$this, 'render_toggle_field'],
            'nk_ai_rankmath_options',
            'general',
            ['key' => 'auto_suggest']
        );
        
        // AI Features section
        add_settings_section(
            'features',
            __('AI Features', 'nk-ai-rankmath'),
            [$this, 'render_features_section'],
            'nk_ai_rankmath_options'
        );
        
        $features = [
            'seo_title' => __('SEO Title', 'nk-ai-rankmath'),
            'meta_description' => __('Meta Description', 'nk-ai-rankmath'),
            'focus_keyword' => __('Focus Keyword', 'nk-ai-rankmath'),
            'keyword_suggestions' => __('Keyword Suggestions', 'nk-ai-rankmath'),
            'schema_generation' => __('Schema Generation', 'nk-ai-rankmath'),
            'faq_generation' => __('FAQ Generation', 'nk-ai-rankmath'),
            'internal_links' => __('Internal Links', 'nk-ai-rankmath'),
            'image_alt' => __('Image ALT Text', 'nk-ai-rankmath'),
            'readability_improvement' => __('Readability Improvement', 'nk-ai-rankmath'),
            'content_optimization' => __('Content Optimization', 'nk-ai-rankmath'),
            'seo_score_improvement' => __('SEO Score Improvement', 'nk-ai-rankmath'),
            'bulk_optimization' => __('Bulk Optimization', 'nk-ai-rankmath'),
        ];
        
        foreach ($features as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'render_toggle_field'],
                'nk_ai_rankmath_options',
                'features',
                ['key' => "features_{$key}"]
            );
        }
    }
    
    public function render_settings_page() {
        $options = get_option($this->option_name, []);
        ?>
        <div class="wrap nk-ai-settings">
            <h1><?php _e('NK AI for Rank Math', 'nk-ai-rankmath'); ?></h1>
            
            <?php settings_errors(); ?>
            
            <div class="nk-ai-settings-wrapper">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('nk_ai_rankmath_options');
                    do_settings_sections('nk_ai_rankmath_options');
                    submit_button();
                    ?>
                </form>
                
                <div class="nk-ai-settings-sidebar">
                    <div class="nk-ai-status-box">
                        <h3><?php _e('System Status', 'nk-ai-rankmath'); ?></h3>
                        <ul>
                            <li>
                                <strong><?php _e('Plugin Version:', 'nk-ai-rankmath'); ?></strong>
                                <?php echo NK_AI_RANKMATH_VERSION; ?>
                            </li>
                            <li>
                                <strong><?php _e('Rank Math:', 'nk-ai-rankmath'); ?></strong>
                                <?php echo defined('RANK_MATH_VERSION') ? 
                                    '<span style="color: green;">✓ ' . RANK_MATH_VERSION . '</span>' :
                                    '<span style="color: red;">✗ Not installed</span>'; ?>
                            </li>
                            <li>
                                <strong><?php _e('AI Gateway:', 'nk-ai-rankmath'); ?></strong>
                                <?php echo $this->check_gateway_status(); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function check_gateway_status() {
        $gateway = \NK_AI_RankMath\AI\Gateway::get_instance();
        // Simple test
        $test = $gateway::run('test', 'test_prompt', 'test content');
        return $test['success'] ? 
            '<span style="color: green;">✓ Connected</span>' :
            '<span style="color: red;">✗ Not connected</span>';
    }
    
    public function render_toggle_field($args) {
        $options = get_option($this->option_name, []);
        $key = $args['key'];
        $value = isset($options[$key]) ? $options[$key] : 1;
        ?>
        <label class="switch">
            <input type="checkbox" name="<?php echo $this->option_name; ?>[<?php echo $key; ?>]" 
                   value="1" <?php checked(1, $value); ?>>
            <span class="slider round"></span>
        </label>
        <?php
    }
    
    public function render_general_section() {
        echo '<p>' . __('Configure general NK AI settings.', 'nk-ai-rankmath') . '</p>';
    }
    
    public function render_features_section() {
        echo '<p>' . __('Enable or disable specific AI features.', 'nk-ai-rankmath') . '</p>';
    }
    
    public function validate_settings($input) {
        // Sanitize and validate settings
        foreach ($input as $key => $value) {
            $input[$key] = sanitize_text_field($value);
        }
        return $input;
    }
}