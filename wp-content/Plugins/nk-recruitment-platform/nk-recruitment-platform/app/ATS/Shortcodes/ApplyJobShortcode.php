<?php

declare(strict_types=1);

namespace NKRecruitment\ATS\Shortcodes;

use NKRecruitment\ATS\Models\Application;
use NKRecruitment\ATS\Services\ApplicationService;

if (!defined('ABSPATH')) {
    exit;
}

class ApplyJobShortcode
{
    public function register(): void
    {
        add_shortcode('nk_apply_job', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleSubmission']);
    }

    public function render(array $atts = []): string
    {
        $job_id = isset($atts['id']) ? (int) $atts['id'] : (isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0);

        if ($job_id === 0) {
            return '<div class="nkrp-alert nkrp-alert-error">Invalid Job ID.</div>';
        }

        if (!is_user_logged_in()) {
            $login_url = esc_url(add_query_arg('redirect_to', urlencode($this->getCurrentUrl()), home_url('/login/')));
            return '
            <div class="nkrp-auth-prompt" style="padding:30px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; text-align:center; margin: 20px 0;">
                <h3 style="margin: 0 0 10px 0; font-size: 18px;">Ready to apply?</h3>
                <p style="color: #64748b; margin-bottom: 20px; font-size: 14px;">Log in or create a Candidate account to submit your application.</p>
                <a href="' . $login_url . '" style="background: #2563eb; color: #ffffff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">Log In to Apply</a>
            </div>';
        }

        $user = wp_get_current_user();
        if (!in_array('nkrp_candidate', (array) $user->roles) && !in_array('candidate', (array) $user->roles)) {
            return '<div class="nkrp-alert nkrp-alert-error">Only Candidates can apply for jobs.</div>';
        }

        global $wpdb;
        $resume_table = $wpdb->prefix . 'nkrp_resumes';
        $suppress = $wpdb->suppress_errors();
        $candidate_resumes = $wpdb->get_results($wpdb->prepare(
            "SELECT id, resume_title, is_primary FROM {$resume_table} WHERE user_id = %d ORDER BY is_primary DESC, id DESC", 
            $user->ID
        ));
        $wpdb->suppress_errors($suppress);

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/ATS/Views/frontend-apply-job.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div class="nkrp-alert nkrp-alert-error">Application view missing.</div>';
        }
        
        return ob_get_clean();
    }

    public function handleSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nkrp_apply_job_submit'])) return;
        
        if (!isset($_POST['nkrp_apply_job_nonce']) || !wp_verify_nonce($_POST['nkrp_apply_job_nonce'], 'nkrp_apply_action')) {
            wp_die('Security check failed. Please refresh and try again.');
        }

        $user = wp_get_current_user();
        if (!is_user_logged_in() || (!in_array('nkrp_candidate', (array) $user->roles) && !in_array('candidate', (array) $user->roles))) {
            wp_die('Unauthorized action.');
        }

        $job_id = isset($_POST['job_id']) ? (int) $_POST['job_id'] : 0;
        $resume_id = isset($_POST['resume_id']) ? (int) $_POST['resume_id'] : 0;
        
        if ($job_id === 0 || $resume_id === 0) {
            wp_redirect(add_query_arg('apply_error', 'missing_data', wp_get_referer()));
            exit;
        }

        global $wpdb;
        $job_table = $wpdb->prefix . 'nkrp_jobs';
        
        // 🔥 10X FIX: Changed 'title' to 'job_title' so the SQL query succeeds!
        $job_row = $wpdb->get_row($wpdb->prepare("SELECT job_title, company_id, notification_email, user_id FROM {$job_table} WHERE id = %d", $job_id));
        $company_id = $job_row ? (int) $job_row->company_id : 0;

        $application = new Application();
        $application->job_id       = $job_id;
        $application->candidate_id = get_current_user_id();
        $application->company_id   = $company_id;
        $application->resume_id    = $resume_id;
        $application->cover_letter = sanitize_textarea_field($_POST['cover_letter'] ?? '');
        $application->status       = 'new';
        $application->employer_rating = 0;

        $service = new ApplicationService();
        $appId = $service->create($application);

        if ($appId) {
            
            // ==========================================
            // 🔥 ENTERPRISE EMAIL ROUTER
            // ==========================================
            if ($job_row) {
                
                // 1. Send Alert to Employer
                $employer_email = '';
                $employer_name = 'Employer';
                
                if (!empty($job_row->notification_email)) {
                    $employer_email = $job_row->notification_email;
                } else {
                    $owner_id = (int)($job_row->user_id ?? 0);
                    if ($owner_id > 0) {
                        $owner = get_userdata($owner_id);
                        if ($owner) {
                            $employer_email = $owner->user_email;
                            $employer_name = $owner->display_name;
                        }
                    }
                }

                if (!empty($employer_email)) {
                    $candidate_name = $user->display_name;
                    $job_title = $job_row->job_title; // Fixed reference
                    
                    $emp_subject = "New Application: {$candidate_name} applied for {$job_title}";
                    
                    $emp_body = "<h3>You have received a new application on NatunKicho!</h3>";
                    $emp_body .= "<p><strong>Role:</strong> {$job_title}<br>";
                    $emp_body .= "<strong>Applicant:</strong> {$candidate_name}</p>";
                    $emp_body .= "<p>Please log in to your ATS Dashboard to review their resume and cover letter:</p>";
                    $emp_body .= "<p><a href='" . esc_url(home_url('/employer-dashboard/?tab=ats')) . "'>View Application in Dashboard</a></p>";
                    $emp_body .= "<br><p>Best regards,<br>The NatunKicho Team</p>";

                    // NK Email Queue Fallback Logic
                    if (class_exists('NK_Email_Queue')) {
                        \NK_Email_Queue::enqueue($employer_email, $employer_name, $emp_subject, $emp_body, ['priority' => 'high']);
                    } else {
                        add_filter('wp_mail_content_type', function() { return 'text/html'; });
                        wp_mail($employer_email, $emp_subject, $emp_body);
                        remove_filter('wp_mail_content_type', function() { return 'text/html'; });
                    }
                }

                // 2. Send Confirmation to Candidate
                $candidate_email = $user->user_email;
                $candidate_name = $user->display_name;
                $cand_subject = "Application Submitted: {$job_row->job_title}";
                
                $cand_body = "<h3>Hello {$candidate_name},</h3>";
                $cand_body .= "<p>Your application for '<strong>{$job_row->job_title}</strong>' has been successfully submitted to the employer.</p>";
                $cand_body .= "<p>You can track your application status anytime in your Candidate Dashboard:</p>";
                $cand_body .= "<p><a href='" . esc_url(home_url('/dashboard/?tab=applied-jobs')) . "'>Track Application Status</a></p>";
                $cand_body .= "<br><p>Good luck!<br>The NatunKicho Team</p>";
                
                if (class_exists('NK_Email_Queue')) {
                    \NK_Email_Queue::enqueue($candidate_email, $candidate_name, $cand_subject, $cand_body, ['priority' => 'high']);
                } else {
                    add_filter('wp_mail_content_type', function() { return 'text/html'; });
                    wp_mail($candidate_email, $cand_subject, $cand_body);
                    remove_filter('wp_mail_content_type', function() { return 'text/html'; });
                }
            }

            wp_redirect(home_url('/candidate-dashboard/?applied=1'));
            exit;
        } else {
            wp_redirect(add_query_arg('apply_error', 'failed', wp_get_referer()));
            exit;
        }
    }

    private function getCurrentUrl(): string {
        global $wp;
        return home_url(add_query_arg(array(), $wp->request));
    }
}