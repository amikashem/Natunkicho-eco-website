<?php

declare(strict_types=1);

namespace NKRecruitment\AI\Controllers;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class AIController
{
   public function dashboard(): void
    {
        // 1. Handle API Key Saving
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nkrp_ai_nonce'])) {
            check_admin_referer('save_ai_settings', 'nkrp_ai_nonce');
            
             if (isset($_POST['openai_key'])) update_option('nkrp_openai_key', sanitize_text_field($_POST['openai_key']));
            if (isset($_POST['gemini_key'])) update_option('nkrp_gemini_key', sanitize_text_field($_POST['gemini_key']));
            if (isset($_POST['grok_key'])) update_option('nkrp_grok_key', sanitize_text_field($_POST['grok_key']));
            if (isset($_POST['github_key'])) update_option('nkrp_github_key', sanitize_text_field($_POST['github_key']));
            
            wp_redirect(admin_url('admin.php?page=nkrp-ai-core&msg=saved'));
            exit;
        }

        global $wpdb;
        $table = DatabaseManager::table('ai_logs');

        // 2. Fetch Telemetry Data
        $total_cost = $wpdb->get_var("SELECT SUM(estimated_cost) FROM {$table}") ?: '0.000000';
        $total_tokens = $wpdb->get_var("SELECT SUM(total_tokens) FROM {$table}") ?: '0';
        $total_requests = $wpdb->get_var("SELECT COUNT(id) FROM {$table}") ?: '0';

        $logs = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 50") ?: [];

        // 3. Get Current Database Keys
       $current_openai = get_option('nkrp_openai_key', '');
        $current_gemini = get_option('nkrp_gemini_key', '');
        $current_grok = get_option('nkrp_grok_key', '');
        $current_github = get_option('nkrp_github_key', '');

        // Require the view
        require NKRP_PLUGIN_PATH . 'app/AI/Views/dashboard.php';
    }
}