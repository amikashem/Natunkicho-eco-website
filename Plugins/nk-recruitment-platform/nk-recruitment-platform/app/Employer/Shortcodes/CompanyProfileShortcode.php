<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class CompanyProfileShortcode
{
    public function register(): void
    {
        add_shortcode('nk_company_profile', [$this, 'render']);
    }

    public function render(): string
    {
        $company_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $company_slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';

        if ($company_id <= 0 && empty($company_slug)) {
            return '<div style="padding:40px; text-align:center; background:#f8fafc; border-radius:12px; color:#64748b;"><h2>Company Not Found</h2><p>Please provide a valid company link.</p></div>';
        }

        global $wpdb;
        $company_table = $wpdb->prefix . 'nkrp_companies';
        $jobs_table = $wpdb->prefix . 'nkrp_jobs';
        
        $suppress = $wpdb->suppress_errors();

        // 1. Fetch Company Data
        if ($company_id > 0) {
            $company = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$company_table} WHERE id = %d AND status = 'active'", $company_id));
        } else {
            $company = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$company_table} WHERE company_slug = %s AND status = 'active'", $company_slug));
        }

        if (!$company) {
            return '<div style="padding:40px; text-align:center; background:#f8fafc; border-radius:12px; color:#64748b;"><h2>Company Unavailable</h2><p>This company profile is not active or has been removed.</p></div>';
        }

        // 2. Fetch Active Jobs for this Company (FIXED: Added 'publish' & fetch all columns)
        $active_jobs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$jobs_table} 
            WHERE company_id = %d AND status IN ('publish', 'published', 'active') 
            ORDER BY featured DESC, created_at DESC
        ", $company->id));

        $wpdb->suppress_errors($suppress);

        // 3. Pass data to template via globals
        global $nkrp_current_company;
        global $nkrp_company_jobs;
        
        $nkrp_current_company = $company;
        $nkrp_company_jobs = $active_jobs;

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'templates/public/company-profile.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo "Error: Company template missing.";
        }
        
        return ob_get_clean();
    }
}