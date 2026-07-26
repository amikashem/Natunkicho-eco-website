<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Shortcodes;

use NKRecruitment\Jobs\Models\Job;
use NKRecruitment\Jobs\Services\JobService;

if (!defined('ABSPATH')) {
    exit;
}

class JobDetailsShortcode
{
    private JobService $service;

    public function __construct()
    {
        $this->service = new JobService();
    }

    public function register(): void
    {
        add_shortcode('nk_job_details', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleApplication']);
    }

    public function render(): string
    {
        global $wpdb;
        $jobs_table = $wpdb->prefix . 'nkrp_jobs';

        $job_slug = get_query_var('job_slug');
        $job_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if (!empty($job_slug)) {
            $found_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$jobs_table} WHERE job_slug = %s LIMIT 1", $job_slug));
            if ($found_id) {
                $job_id = (int) $found_id;
            }
        }
        
        ob_start();

        if ($job_id <= 0) {
            echo '<div style="padding:40px; text-align:center; background:#f8fafc; border-radius:12px; color:#64748b; margin: 40px auto; max-width: 1200px;"><h2>Job Not Found</h2><p>This job listing may have been removed or expired.</p></div>';
        } else {
            $job = $this->service->find($job_id);
            
            if (!$job) {
                echo '<div style="padding:40px; text-align:center; background:#f8fafc; border-radius:12px; color:#64748b; margin: 40px auto; max-width: 1200px;"><h2>Job Unavailable</h2><p>This job is no longer accepting applications.</p></div>';
            } else {
                $status_lower = strtolower($job->status);
                $is_active = in_array($status_lower, ['active', 'published', 'publish']);
                $is_admin = current_user_can('manage_options');
                $current_user_id = get_current_user_id();
                $job_owner = isset($job->user_id) ? (int)$job->user_id : (isset($job->employer_id) ? (int)$job->employer_id : 0);
                $is_owner = ($current_user_id > 0 && $current_user_id === $job_owner);

                if (!$is_active && !$is_admin && !$is_owner) {
                    echo '<div style="padding:40px; text-align:center; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; color:#991b1b; margin: 40px auto; max-width: 1200px;"><h2>Job Unavailable</h2><p>This job is currently pending approval, paused, or no longer accepting applications.</p></div>';
                } else {

                    $preview_notice = '';
                    if (!$is_active && ($is_owner || $is_admin)) {
                        $preview_notice = '<div style="background:#fef08a; color:#854d0e; padding:12px; text-align:center; font-weight:bold; border-bottom:1px solid #fde047;">Preview Mode: This job is currently marked as ' . esc_html(ucfirst($job->status)) . ' and is not visible to candidates.</div>';
                    }

                    $department = isset($job->department) ? $job->department : '';
                    $related_jobs = [];
                    if (!empty($department)) {
                        $related_jobs = $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM {$jobs_table} 
                            WHERE department = %s 
                            AND id != %d 
                            AND status IN ('publish', 'published', 'active') 
                            ORDER BY created_at DESC LIMIT 3
                        ", $department, $job->id));
                    }

                    global $nkrp_current_job, $nkrp_related_jobs;
                    $nkrp_current_job = $job;
                    $nkrp_related_jobs = $related_jobs;

                    echo $preview_notice;
                    
                    $templatePath = NKRP_PLUGIN_PATH . 'templates/public/job-details.php';

                    if (function_exists('nkrp_safe_render_view')) {
                        $scope_vars = get_defined_vars();
                        nkrp_safe_render_view($templatePath, $scope_vars);
                    } else {
                        if (file_exists($templatePath)) {
                            require $templatePath;
                        } else {
                            echo '<div class="nkrp-alert nkrp-alert-error">Error: Job template missing.</div>';
                        }
                    }
                }
            }
        }

        return ob_get_clean();
    }

    public function handleApplication(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $user_id = get_current_user_id();

        if (isset($_POST['nkrp_save_job'])) {
            if (!is_user_logged_in() || !in_array('nkrp_candidate', (array) wp_get_current_user()->roles)) {
                wp_redirect(esc_url(home_url('/login/?redirect_to=' . urlencode($_SERVER['REQUEST_URI']))));
                exit;
            }
            if (!wp_verify_nonce($_POST['nkrp_job_action_nonce'], 'nkrp_job_actions')) wp_die('Security check failed.');

            $job_id = (int) $_POST['job_id'];
            $saved_jobs = get_user_meta($user_id, '_nkrp_saved_jobs', true);
            if (!is_array($saved_jobs)) $saved_jobs = [];
            
            if (!in_array($job_id, $saved_jobs)) {
                $saved_jobs[] = $job_id;
                update_user_meta($user_id, '_nkrp_saved_jobs', array_values($saved_jobs));
            }
            wp_redirect(add_query_arg('job_saved', '1', wp_get_referer()));
            exit;
        }

        if (isset($_POST['nkrp_apply_job'])) {
            if (!is_user_logged_in() || !in_array('nkrp_candidate', (array) wp_get_current_user()->roles)) {
                wp_redirect(esc_url(home_url('/login/?redirect_to=' . urlencode($_SERVER['REQUEST_URI']))));
                exit;
            }
            if (!wp_verify_nonce($_POST['nkrp_job_action_nonce'], 'nkrp_job_actions')) wp_die('Security check failed.');

            global $wpdb;
            $apps_table = $wpdb->prefix . 'nkrp_applications';
            $job_id = (int) $_POST['job_id'];
            $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;

            $has_applied = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$apps_table} WHERE candidate_id = %d AND job_id = %d", $user_id, $job_id));
            if ($has_applied) {
                wp_redirect(add_query_arg('already_applied', '1', wp_get_referer()));
                exit;
            }

            $wpdb->insert(
                $apps_table,
                [
                    'job_id' => $job_id,
                    'candidate_id' => $user_id,
                    'company_id' => $company_id,
                    'cover_letter' => sanitize_textarea_field($_POST['cover_letter'] ?? ''),
                    'status' => 'pending' 
                ],
                ['%d', '%d', '%d', '%s', '%s']
            );
            do_action('nkrp_candidate_applied', $user_id, $job_id);
            wp_redirect(add_query_arg('application_success', '1', wp_get_referer()));
            exit;
        }
    }
}