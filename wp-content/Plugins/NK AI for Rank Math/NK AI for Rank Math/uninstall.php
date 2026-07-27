<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('nk_ai_rankmath_settings');

// Delete transients
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%nk_ai_rankmath%'");

// Delete cache files
$cache_dir = WP_CONTENT_DIR . '/cache/nk-ai-rankmath/';
if (is_dir($cache_dir)) {
    array_map('unlink', glob($cache_dir . '*'));
    rmdir($cache_dir);
}

// Delete logs
$log_file = WP_CONTENT_DIR . '/logs/nk-ai-rankmath.log';
if (file_exists($log_file)) {
    unlink($log_file);
}

// Drop custom tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}nk_ai_cache");