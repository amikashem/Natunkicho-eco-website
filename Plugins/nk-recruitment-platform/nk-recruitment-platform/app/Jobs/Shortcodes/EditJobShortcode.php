<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Shortcodes;

use NKRecruitment\Jobs\Models\Job;
use NKRecruitment\Jobs\Services\JobService;

if (!defined('ABSPATH')) {
    exit;
}

class EditJobShortcode
{
    public function register(): void
    {
        add_shortcode('nk_edit_job', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleSubmission']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function enqueueScripts(): void
    {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'nk_edit_job')) {
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
        }
    }

    public function render(): string
    {
        if (!is_user_logged_in() || !in_array('nkrp_employer', (array) wp_get_current_user()->roles)) {
            return '<div style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px; max-width: 800px; margin: 0 auto;"><strong>Access Denied:</strong> This page is strictly for Employers.</div>';
        }

        $job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($job_id <= 0) {
            return '<div class="nkrp-alert nkrp-alert-error">Invalid Job ID.</div>';
        }

        $user_id = get_current_user_id();
        $service = new JobService();
        $job = $service->find($job_id);

        // Security Check: Verify Ownership
        if (!$job || (int)$job->employer_id !== $user_id) {
            return '<div class="nkrp-alert nkrp-alert-error">You do not have permission to edit this job.</div>';
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
        $templatePath = NKRP_PLUGIN_PATH . 'app/Jobs/Views/frontend-edit-job.php';
        if (file_exists($templatePath)) { require $templatePath; } 
        return ob_get_clean();
    }

    public function handleSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nkrp_edit_job_submit'])) return;
        if (!isset($_POST['nkrp_edit_job_nonce']) || !wp_verify_nonce($_POST['nkrp_edit_job_nonce'], 'nkrp_edit_job_action')) wp_die('Security check failed.');
        
        $user_id = get_current_user_id();
        $job_id = (int)$_POST['job_id'];

        $service = new JobService();
        $existing_job = $service->find($job_id);

        if (!$existing_job || (int)$existing_job->employer_id !== $user_id) {
            wp_die('Unauthorized action.');
        }

        $job = new Job();
        $job->id               = $job_id;
        $job->title            = sanitize_text_field($_POST['title'] ?? ''); 
        $job->job_type         = sanitize_text_field($_POST['job_type'] ?? '');
        $job->department       = sanitize_text_field($_POST['department'] ?? '');
        $job->location         = sanitize_text_field($_POST['location'] ?? '');
        $job->country          = sanitize_text_field($_POST['country'] ?? '');
        $job->salary_min       = !empty($_POST['salary_min']) ? floatval($_POST['salary_min']) : 0.00;
        $job->salary_max       = !empty($_POST['salary_max']) ? floatval($_POST['salary_max']) : 0.00;
        $job->currency         = 'USD'; 
        $job->vacancies        = !empty($_POST['vacancies']) ? intval($_POST['vacancies']) : 1;
        $job->experience       = sanitize_text_field($_POST['experience'] ?? '');
        $job->education        = sanitize_text_field($_POST['education'] ?? '');
        $job->deadline         = sanitize_text_field($_POST['deadline'] ?? null);
        $job->description      = wp_kses_post($_POST['description'] ?? '');
        $job->requirements     = sanitize_textarea_field($_POST['requirements'] ?? '');
        $job->responsibilities = sanitize_textarea_field($_POST['responsibilities'] ?? '');
        $job->external_apply_url = esc_url_raw($_POST['external_apply_url'] ?? '');
        
        $job->company_id       = (int)$_POST['company_id']; 
        $job->employer_id      = $user_id; 
        
        // Retain current status (or reset to pending if you prefer strict approval on edits)
        $job->status           = $existing_job->status;

        $service->update($job);

        // Redirect back to dashboard successfully
        wp_redirect(home_url('/employer-dashboard/?tab=jobs&job_updated=1'));
        exit;
    }
}