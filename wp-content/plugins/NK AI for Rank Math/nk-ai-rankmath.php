<?php
/**
 * Plugin Name: NK AI for Rank Math
 * Plugin URI: https://nkgroup.com/rankmath-ai
 * Description: AI-powered SEO assistant for Rank Math using your own AI providers
 * Version: 1.0.0
 * Author: NK Group
 * Author URI: https://nkgroup.com
 * License: GPL v2 or later
 * Text Domain: nk-ai-rankmath
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.6
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('NK_AI_RANKMATH_VERSION', '1.0.0');
define('NK_AI_RANKMATH_FILE', __FILE__);
define('NK_AI_RANKMATH_PATH', plugin_dir_path(__FILE__));
define('NK_AI_RANKMATH_URL', plugin_dir_url(__FILE__));
define('NK_AI_RANKMATH_BASENAME', plugin_basename(__FILE__));

// Simple autoloader (no Composer)
spl_autoload_register(function ($class) {
    $prefix = 'NK_AI_RankMath\\';
    $base_dir = NK_AI_RANKMATH_PATH . 'app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Start the plugin
NK_AI_RankMath\Core\Plugin::get_instance();