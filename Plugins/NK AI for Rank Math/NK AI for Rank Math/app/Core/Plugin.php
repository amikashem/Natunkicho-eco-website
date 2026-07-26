<?php
namespace NK_AI_RankMath\Core;

class Plugin {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }
    
    private function init() {
        // Check dependencies
        if (!$this->check_dependencies()) {
            return;
        }
        
        // Load components
        $this->load_components();
        
        // Activation/deactivation hooks
        register_activation_hook(NK_AI_RANKMATH_FILE, [Activator::class, 'activate']);
        register_deactivation_hook(NK_AI_RANKMATH_FILE, [Deactivator::class, 'deactivate']);
    }
    
    private function check_dependencies() {
        // Check if Rank Math is active
        if (!defined('RANK_MATH_VERSION') && !class_exists('RankMath')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo __('NK AI for Rank Math requires Rank Math SEO to be installed and activated.', 'nk-ai-rankmath');
                echo '</p></div>';
            });
            return false;
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo __('NK AI for Rank Math requires PHP 7.4 or higher.', 'nk-ai-rankmath');
                echo '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    private function load_components() {
        // Load helpers first
        require_once NK_AI_RANKMATH_PATH . 'app/Helpers/Helpers.php';
        require_once NK_AI_RANKMATH_PATH . 'app/Helpers/Cache.php';
        require_once NK_AI_RANKMATH_PATH . 'app/Helpers/Logger.php';
        
        // Initialize gateway
        $gateway = \NK_AI_RankMath\AI\Gateway::get_instance();
        add_action('init', [$gateway, 'init']);
        
        // Initialize admin
        $admin = new \NK_AI_RankMath\Admin\Admin();
        add_action('admin_init', [$admin, 'init']);
        
        // Initialize Rank Math integration
        $rankmath = new \NK_AI_RankMath\RankMath\Integration();
        add_action('admin_init', [$rankmath, 'init']);
        add_action('rank_math/admin/header', [$rankmath, 'add_ai_buttons']);
        add_filter('rank_math/admin/editor_scripts', [$rankmath, 'enqueue_scripts']);
        
        // Initialize REST API
        add_action('rest_api_init', [\NK_AI_RankMath\API\REST::class, 'register_routes']);
        
        // Initialize assets
        $assets = new \NK_AI_RankMath\Assets\Manager();
        add_action('admin_enqueue_scripts', [$assets, 'enqueue_admin']);
        add_action('wp_enqueue_scripts', [$assets, 'enqueue_public']);
    }
}