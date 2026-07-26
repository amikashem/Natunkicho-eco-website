<?php
/**
 * Plugin Name: NK Recruitment Platform
 * Plugin URI: https://natunkicho.com
 * Description: Enterprise Recruitment Platform for NatunKicho.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: NatunKicho
 * Text Domain: nk-recruitment
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('NKRP_VERSION', '1.0.0');
define('NKRP_PLUGIN_FILE', __FILE__);
define('NKRP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NKRP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once NKRP_PLUGIN_PATH . 'app/Core/Activator.php';
require_once NKRP_PLUGIN_PATH . 'app/Core/Deactivator.php';

register_activation_hook(
    __FILE__,
    ['NKRecruitment\Core\Activator', 'activate']
);

register_deactivation_hook(
    __FILE__,
    ['NKRecruitment\Core\Deactivator', 'deactivate']
);

require_once NKRP_PLUGIN_PATH . 'bootstrap/bootstrap.php';

// =====================================================
// Global Init Hooks (Shortcodes, etc.)
// =====================================================
add_action('init', function() {
    // Register the Resume Frontend Shortcode [nk_resume id="X"]
    if (class_exists('\NKRecruitment\Resume\Controllers\ResumeController')) {
        $resumeController = new \NKRecruitment\Resume\Controllers\ResumeController();
        $resumeController->registerShortcodes();
    }
});

// =====================================================
// Phase 6: Load Modules and Migration Tools
// =====================================================
add_action('plugins_loaded', function() {
    
    // 1. Load the Legacy WPJM Migration Tool
    if (class_exists('\NKRecruitment\Admin\Controllers\MigrationController')) {
        (new \NKRecruitment\Admin\Controllers\MigrationController())->register();
    }

    // 2. Load the API Service Provider (For Chat / Messages)
    if (class_exists('\NKRecruitment\API\APIServiceProvider')) {
        (new \NKRecruitment\API\APIServiceProvider())->register();
    }

    // 3. Load the Search Controller (CRITICAL FOR SEARCH BUTTON TO WORK)
    if (class_exists('\NKRecruitment\Search\Controllers\SearchController')) {
        (new \NKRecruitment\Search\Controllers\SearchController())->register();
    }
   // Load the Enterprise URL Router & SEO Engine
    if (class_exists('\NKRecruitment\Core\UrlRouter')) {
        (new \NKRecruitment\Core\UrlRouter())->register();
    }
   
    if (class_exists('\NKRecruitment\Core\RankMathBridge')) {
        (new \NKRecruitment\Core\RankMathBridge())->register();
    }
    
    if (class_exists('\NKRecruitment\Candidate\Shortcodes\CandidateProfileShortcode')) {
    (new \NKRecruitment\Candidate\Shortcodes\CandidateProfileShortcode())->register();
    
    // Real-Time Messaging Engine
    if (class_exists('\NKRecruitment\Messages\MessageController')) {
        (new \NKRecruitment\Messages\MessageController())->register();
    }
}
});
// =====================================================
// Global System Fault Isolation Tool (Error Boundary)
// =====================================================
/**
 * Safely includes a view file inside an isolated sandbox.
 * Prevents template syntax/runtime errors from breaking the entire page or site.
 *
 * @param string $file_path Absolute path to the PHP file.
 * @param array  $variables Scope variables to pass down into the template.
 */
function nkrp_safe_render_view(string $file_path, array $variables = []): void {
    // 1. Check if the file exists
    if (!file_exists($file_path)) {
        if (current_user_can('manage_options')) {
            echo '<div style="background:#fff5f5; color:#c53030; padding:15px; border:1px solid #feb2b2; margin:15px 0; border-radius:6px; font-family:sans-serif;">';
            echo '<strong>Admin Error:</strong> File path does not exist:<br><code style="background:#edf2f7; padding:2px 6px;">' . esc_html($file_path) . '</code>';
            echo '</div>';
        }
        return;
    }

    // 2. Extract scope variables to give template access to data
    if (!empty($variables)) {
        extract($variables);
    }

    // 3. Execute inside an isolated exception container
    try {
        include $file_path;
    } 
    catch (\Throwable $e) {
        // Silently capture logs for backend debugging
        error_log('NKRP Isolated View Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

        // 4. Role-based display outputs
        if (current_user_can('manage_options')) {
            echo '<div style="background:#fff5f5; color:#c53030; padding:20px; border-left:4px solid #e53e3e; margin:20px 0; font-family:monospace; border-radius:6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
            echo '<h4 style="margin:0 0 10px 0; color:#9b2c2c;">⚠️ Dashboard View Isolated (Admin View)</h4>';
            echo '<strong>Error:</strong> ' . esc_html($e->getMessage()) . '<br><br>';
            echo '<strong>File:</strong> ' . esc_html($e->getFile()) . '<br>';
            echo '<strong>Line:</strong> ' . esc_html($e->getLine());
            echo '</div>';
        } else {
            echo '<div style="padding:30px; background:#f8fafc; border:1px dashed #cbd5e1; color:#64748b; border-radius:12px; text-align:center; margin:20px 0;">';
            echo '<span class="dashicons dashicons-warning" style="font-size:32px; width:32px; height:32px; color:#94a3b8; margin-bottom:10px;"></span>';
            echo '<p style="margin:0; font-weight:500;">This section is temporarily undergoing maintenance.</p>';
            echo '<p style="margin:5px 0 0 0; font-size:13px; color:#94a3b8;">Our technical team has been automatically notified. Please check back shortly!</p>';
            echo '</div>';
        }
    }
}