<?php

declare(strict_types=1);

namespace NKRecruitment\ATS\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class EmployerATSShortcode
{
    public function register(): void
    {
        add_shortcode('nk_employer_ats', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleStatusUpdate']);
    }

    public function render(): string
    {
        if (!is_user_logged_in() || !in_array('nkrp_employer', (array) wp_get_current_user()->roles)) {
            return '<div class="nkrp-alert nkrp-alert-error" style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px;"><strong>Access Denied:</strong> This dashboard is exclusively for Employers.</div>';
        }

        global $wpdb;
        $employer_id  = get_current_user_id();
        $app_table    = $wpdb->prefix . 'nkrp_applications';
        $job_table    = $wpdb->prefix . 'nkrp_jobs';
        $resume_table = $wpdb->prefix . 'nkrp_resumes';
        $user_table   = $wpdb->users;
        $comp_table   = $wpdb->prefix . 'nkrp_companies';

        $suppress = $wpdb->suppress_errors();
        
       // Massive SaaS Query: Added r.file_path and r.file_type
        $applications = $wpdb->get_results($wpdb->prepare("
            SELECT a.id as app_id, a.status, a.created_at, 
                   j.title as job_title, 
                   r.resume_title, r.id as resume_id, r.file_path, r.file_type,
                   u.display_name as candidate_name, u.user_email as candidate_email
            FROM {$app_table} a
            JOIN {$job_table} j ON a.job_id = j.id
            JOIN {$resume_table} r ON a.resume_id = r.id
            JOIN {$user_table} u ON a.candidate_id = u.ID
            JOIN {$comp_table} c ON a.company_id = c.id
            WHERE c.user_id = %d
            ORDER BY a.id DESC
        ", $employer_id));
        
        $wpdb->suppress_errors($suppress);

        // Group applications by status for the Kanban board
        $board = [
            'new' => [],
            'screening' => [],
            'interview' => [],
            'hired' => [],
            'rejected' => []
        ];

        if ($applications) {
            foreach ($applications as $app) {
                $status = $app->status;
                if (!array_key_exists($status, $board)) {
                    $status = 'new'; // Fallback
                }
                $board[$status][] = $app;
            }
        }

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/ATS/Views/employer-ats-dashboard.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div class="nkrp-alert nkrp-alert-error">ATS Dashboard view missing.</div>';
        }
        
        return ob_get_clean();
    }

    public function handleStatusUpdate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nkrp_ats_update_status'])) return;

        if (!isset($_POST['nkrp_ats_nonce']) || !wp_verify_nonce($_POST['nkrp_ats_nonce'], 'nkrp_ats_action')) {
            wp_die('Security check failed.');
        }

        if (!is_user_logged_in() || !in_array('nkrp_employer', (array) wp_get_current_user()->roles)) {
            wp_die('Unauthorized action.');
        }

        global $wpdb;
        $app_table = $wpdb->prefix . 'nkrp_applications';
        
        $app_id = (int) $_POST['application_id'];
        $new_status = sanitize_text_field($_POST['new_status']);

        // Update the application status in the database
        $wpdb->update(
            $app_table,
            ['status' => $new_status],
            ['id' => $app_id],
            ['%s'],
            ['%d']
        );

        // Redirect back to the ATS board to see the change
        wp_redirect(add_query_arg('status_updated', '1', wp_get_referer()));
        exit;
    }
}