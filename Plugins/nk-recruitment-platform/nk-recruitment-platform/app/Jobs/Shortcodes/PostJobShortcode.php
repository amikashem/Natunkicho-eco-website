<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class PostJobShortcode
{
    public function register(): void
    {
        add_shortcode('nk_post_job', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        
        // 🔥 THE BRIDGE: Wakes up the JobController on the frontend!
        add_action('template_redirect', [$this, 'routeSubmission']);
    }

    public function routeSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nkrp_action']) && $_POST['nkrp_action'] === 'edit_job_submit') {
            $controller = new \NKRecruitment\Jobs\Controllers\JobController();
            $controller->handleFrontendSubmit();
        }
    }

    public function enqueueScripts(): void
    {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'nk_post_job')) {
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
        }
    }

    public function render(): string
    {
        if (!is_user_logged_in()) {
            $login_url = esc_url(home_url('/login/'));
            $register_url = esc_url(home_url('/register/?intent=employer')); 
            return '
            <div class="nkrp-auth-prompt" style="padding:40px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; text-align:center; max-width: 500px; margin: 40px auto;">
                <span class="dashicons dashicons-lock" style="font-size: 48px; color: #64748b; margin-bottom: 20px;"></span>
                <h3 style="margin: 0 0 10px 0; font-size: 22px;">Employer Access Required</h3>
                <p style="color: #64748b; margin-bottom: 24px;">You must be logged in as an employer to post a new job opening.</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <a href="' . $login_url . '" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">Log In</a>
                    <a href="' . $register_url . '" style="background: #2563eb; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">Register as Employer</a>
                </div>
            </div>';
        }

        if (!in_array('nkrp_employer', (array) wp_get_current_user()->roles) && !in_array('employer', (array) wp_get_current_user()->roles)) {
            return '<div style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px; max-width: 800px; margin: 0 auto;"><strong>Access Denied:</strong> This page is strictly for Employers.</div>';
        }

        $user_id = get_current_user_id();
        $is_premium = apply_filters('nkrp_is_user_premium', false, $user_id);
        
        $active_jobs_count = count_user_posts($user_id, 'nk_job', true); 
        $free_job_limit = 1;

        if (!$is_premium && $active_jobs_count >= $free_job_limit) {
            return '
            <div class="nkrp-auth-prompt" style="padding:40px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; text-align:center; max-width: 600px; margin: 40px auto;">
                <span class="dashicons dashicons-lock" style="font-size: 48px; color: #fbbf24; margin-bottom: 20px;"></span>
                <h3 style="margin: 0 0 10px 0; font-size: 22px;">Job Posting Limit Reached</h3>
                <p style="color: #64748b; margin-bottom: 24px;">You have reached the maximum number of active jobs allowed on the Free plan ('. $free_job_limit .'). Please upgrade to a Premium Hiring Package to post unlimited roles.</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <a href="' . esc_url(home_url('/employer-dashboard/')) . '" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">Return to Dashboard</a>
                    <a href="' . esc_url(home_url('/membership/')) . '" style="background: #fbbf24; color: #78350f; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700;">Upgrade to Premium</a>
                </div>
            </div>';
        }

        $raw_countries = get_option('nkrp_global_countries', "United States\nUnited Kingdom\nCanada\nAustralia");
        $countries_array = array_filter(array_map('trim', explode("\n", $raw_countries)));
        $raw_departments = get_option('nkrp_global_departments', "Management\nFood & Beverage\nCulinary");
        $departments_array = array_filter(array_map('trim', explode("\n", $raw_departments)));

        global $wpdb;
        $company_table = $wpdb->prefix . 'nkrp_companies';
        $suppress = $wpdb->suppress_errors();
        $employer_companies = $wpdb->get_results($wpdb->prepare(
            "SELECT id, company_name FROM {$company_table} WHERE user_id = %d ORDER BY company_name ASC",
            $user_id
        ));
        $wpdb->suppress_errors($suppress);

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Jobs/Views/frontend-post-job.php';
        if (file_exists($templatePath)) { require $templatePath; } 
        return ob_get_clean();
    }
}

if (!function_exists('getCurrentUrl')) {
    function getCurrentUrl(): string {
        global $wp;
        return home_url(add_query_arg(array(), $wp->request));
    }
}