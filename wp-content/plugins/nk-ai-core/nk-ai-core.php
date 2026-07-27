<?php
/**
 * Plugin Name: NatunKicho AI Core Platform
 * Description: The centralized AI Gateway and Intelligence Engine for the NatunKicho Ecosystem.
 * Version: 1.0.0
 * Author: NatunKicho Enterprise Architecture
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'NK_AI_CORE_VERSION', '1.0.0' );
define( 'NK_AI_CORE_DIR', plugin_dir_path( __FILE__ ) );

// Load Core Components
require_once NK_AI_CORE_DIR . 'includes/class-nk-ai-db.php';
require_once NK_AI_CORE_DIR . 'includes/class-nk-ai-admin.php';
require_once NK_AI_CORE_DIR . 'includes/class-nk-ai-gateway.php'; 

class NK_AI_Core_Bootstrapper {
    
    public static function init() {
        // Initialize Admin UI
        if ( is_admin() ) {
            NK_AI_Admin::init();
        }
    }

    public static function activate() {
        // Build Custom Database Tables on Activation
        NK_AI_DB::create_tables();
    }
}

// Hook Activation
register_activation_hook( __FILE__, array( 'NK_AI_Core_Bootstrapper', 'activate' ) );

// Boot the Engine
add_action( 'plugins_loaded', array( 'NK_AI_Core_Bootstrapper', 'init' ) );