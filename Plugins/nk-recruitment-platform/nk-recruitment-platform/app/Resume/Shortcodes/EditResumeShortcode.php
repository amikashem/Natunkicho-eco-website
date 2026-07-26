<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Shortcodes;

use NKRecruitment\Resume\Models\Resume;
use NKRecruitment\Resume\Services\ResumeService;

if (!defined('ABSPATH')) {
    exit;
}

class EditResumeShortcode
{
    public function register(): void
    {
        add_shortcode('nk_edit_resume', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleSubmission']);
    }

    public function render(): string
    {
        if (!is_user_logged_in() || !in_array('nkrp_candidate', (array) wp_get_current_user()->roles)) {
            return '<div class="nkrp-alert nkrp-alert-error"><strong>Access Denied:</strong> You must be logged in as a Candidate to edit a resume.</div>';
        }

        $resume_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        if ($resume_id === 0) {
            return '<div class="nkrp-alert nkrp-alert-error">Invalid Resume ID.</div>';
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_resumes';
        $user_id = get_current_user_id();
        
        // Security: Ensure this candidate actually owns this resume
        $resume_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d AND user_id = %d", $resume_id, $user_id));

        if (!$resume_data) {
            return '<div class="nkrp-alert nkrp-alert-error">Resume not found or you do not have permission to edit it.</div>';
        }

        // Decode JSON arrays so the View can use them
        $experience_array = json_decode($resume_data->experience_data, true) ?: [];
        $education_array = json_decode($resume_data->education_data, true) ?: [];
        $skills_array = json_decode($resume_data->skills_data, true) ?: [];
        $skills_string = implode(', ', $skills_array);

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Resume/Views/frontend-resume-edit.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div class="nkrp-alert nkrp-alert-error">Edit Resume template missing.</div>';
        }
        
        return ob_get_clean();
    }

    public function handleSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nkrp_edit_resume_submit'])) return;
        if (!isset($_POST['nkrp_edit_resume_nonce']) || !wp_verify_nonce($_POST['nkrp_edit_resume_nonce'], 'nkrp_edit_resume_action')) {
            wp_die('Security check failed. Please go back and try again.');
        }
        if (!is_user_logged_in() || !in_array('nkrp_candidate', (array) wp_get_current_user()->roles)) wp_die('Unauthorized.');

        $resume_id = (int) $_POST['resume_id'];
        $user_id = get_current_user_id();

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_resumes';
        
        // Ensure ownership again before saving
        $owner_check = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d AND user_id = %d", $resume_id, $user_id));
        if (!$owner_check) wp_die('Security Violation: You do not own this resume.');

        // ==========================================
        // SECURE FILE UPLOAD LOGIC (PDF/DOCX)
        // ==========================================
        $file_path = sanitize_text_field($_POST['existing_file_path'] ?? '');
        
        if (!empty($_FILES['resume_file']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            
            $uploaded_file = $_FILES['resume_file'];
            $allowed_mimes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            
            // Check file type securely
            $file_info = wp_check_filetype(basename($uploaded_file['name']), $allowed_mimes);
            
            if (empty($file_info['ext'])) {
                wp_die('Invalid file type. Only PDF and Word documents are allowed.');
            }
            
            // 5MB Max Size Limit
            if ($uploaded_file['size'] > 5 * 1024 * 1024) {
                wp_die('File size exceeds the 5MB limit.');
            }

            // Upload the file securely to WordPress uploads directory
            $upload_overrides = ['test_form' => false, 'mimes' => $allowed_mimes];
            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $file_path = $movefile['url']; // Save the secure URL to database
            } else {
                wp_die($movefile['error']);
            }
        }

        // ==========================================
        // DYNAMIC JSON ARRAY HANDLING
        // ==========================================
        $experience_array = [];
        if (isset($_POST['experience']) && is_array($_POST['experience'])) {
            foreach ($_POST['experience'] as $exp) {
                if (!empty($exp['job_title']) && !empty($exp['company'])) {
                    $start_date = sanitize_text_field($exp['start_month'] ?? '') . ' ' . sanitize_text_field($exp['start_year'] ?? '');
                    $end_date = (isset($exp['current']) && $exp['current'] === 'on') ? 'Present' : sanitize_text_field($exp['end_month'] ?? '') . ' ' . sanitize_text_field($exp['end_year'] ?? '');

                    $experience_array[] = [
                        'job_title'   => sanitize_text_field($exp['job_title']),
                        'company'     => sanitize_text_field($exp['company']),
                        'start_date'  => trim($start_date),
                        'end_date'    => trim($end_date),
                        'description' => sanitize_textarea_field($exp['description'] ?? '')
                    ];
                }
            }
        }

        $education_array = [];
        if (isset($_POST['education']) && is_array($_POST['education'])) {
            foreach ($_POST['education'] as $edu) {
                if (!empty($edu['degree']) && !empty($edu['institution'])) {
                    $education_array[] = [
                        'degree'      => sanitize_text_field($edu['degree']),
                        'institution' => sanitize_text_field($edu['institution']),
                        'grad_year'   => sanitize_text_field($edu['grad_year'] ?? '')
                    ];
                }
            }
        }

        $raw_skills = sanitize_text_field($_POST['skills'] ?? '');
        $skills_array = array_filter(array_map('trim', explode(',', $raw_skills)));

        // Update Database
        $wpdb->update(
            $table,
            [
                'resume_title' => sanitize_text_field($_POST['resume_title'] ?? ''),
                'objective' => sanitize_textarea_field($_POST['objective'] ?? ''),
                'experience_data' => wp_json_encode($experience_array),
                'education_data' => wp_json_encode($education_array),
                'skills_data' => wp_json_encode(array_values($skills_array)),
                'file_path' => $file_path,
                'file_type' => empty($file_path) ? 'manual' : 'pdf_upload'
            ],
            ['id' => $resume_id],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        wp_redirect(home_url('/candidate-dashboard/?resume_updated=1'));
        exit;
    }
}