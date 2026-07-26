<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Shortcodes;

use NKRecruitment\Resume\Models\Resume;
use NKRecruitment\Resume\Services\ResumeService;
// NEW: Import the Gatekeeper
use NKRecruitment\Membership\Services\PermissionService;

if (!defined('ABSPATH')) {
    exit;
}

class CreateResumeShortcode
{
    public function register(): void
    {
        add_shortcode('nk_create_resume', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleSubmission']);
    }

    public function render(): string
    {
        if (!is_user_logged_in() || !in_array('nkrp_candidate', (array) wp_get_current_user()->roles)) {
            return '<div style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px; max-width:800px; margin:0 auto;"><strong>Access Denied:</strong> You must be logged in as a Candidate to build a resume.</div>';
        }

        $user_id = get_current_user_id();
        $permissionService = new PermissionService();

        // ==========================================
        // PAYWALL: Enforce Candidate CV Limits
        // ==========================================
        if (!$permissionService->canCreateResume($user_id)) {
            return '
            <div class="nkrp-paywall-container" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 40px; text-align: center; max-width: 600px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <span class="dashicons dashicons-lock" style="font-size: 48px; width: 48px; height: 48px; color: #d97706; margin-bottom: 20px;"></span>
                <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 24px;">Resume Limit Reached</h3>
                <p style="color: #b45309; font-size: 16px; margin-bottom: 30px; line-height: 1.6;">You have reached the maximum number of active resumes allowed on your Free plan. Upgrade to a Premium account to create up to 5 tailored CVs, unlock AI writing assistance, and get featured placement in employer searches!</p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="' . esc_url(home_url('/candidate-dashboard/')) . '" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.2s;">Back to Dashboard</a>
                    <a href="' . esc_url(home_url('/pricing/')) . '" style="background: #d97706; color: #fff; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.2s;">View Premium Plans</a>
                </div>
            </div>';
        }

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Resume/Views/frontend-resume-builder.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px;"><strong>Error:</strong> The frontend-resume-builder.php view is missing.</div>';
        }
        
        return ob_get_clean();
    }

    public function handleSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nkrp_create_resume_submit'])) return;
        
        if (!isset($_POST['nkrp_create_resume_nonce']) || !wp_verify_nonce($_POST['nkrp_create_resume_nonce'], 'nkrp_create_resume_action')) {
            wp_die('Security check failed. Please go back, refresh the page, and try again. (If this persists, disable caching on this page).');
        }
        
        if (!is_user_logged_in() || !in_array('nkrp_candidate', (array) wp_get_current_user()->roles)) {
            wp_die('Unauthorized action.');
        }

        $user_id = get_current_user_id();
        $permissionService = new PermissionService();

        // SECURITY PAYWALL: Stop users from trying to bypass the UI using HTTP POST requests
        if (!$permissionService->canCreateResume($user_id)) {
            wp_die('Resume limit reached. Please upgrade your account to create more resumes.');
        }

        $resume = new Resume();
        $resume->user_id      = $user_id;
        $resume->candidate_id = 0; 
        $resume->resume_title = sanitize_text_field($_POST['resume_title'] ?? 'My Professional Resume');
        $resume->objective    = sanitize_textarea_field($_POST['objective'] ?? '');
        
        // ==========================================
        // SECURE FILE UPLOAD LOGIC
        // ==========================================
        $file_path = null;
        $file_type = 'manual';

        if (!empty($_FILES['resume_file']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            
            $uploaded_file = $_FILES['resume_file'];
            $allowed_mimes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            
            $file_info = wp_check_filetype(basename($uploaded_file['name']), $allowed_mimes);
            
            if (empty($file_info['ext'])) {
                wp_die('Invalid file type. Only PDF and Word documents are allowed.');
            }
            
            if ($uploaded_file['size'] > 5 * 1024 * 1024) {
                wp_die('File size exceeds the 5MB limit.');
            }

            $upload_overrides = ['test_form' => false, 'mimes' => $allowed_mimes];
            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $file_path = $movefile['url']; 
                $file_type = 'pdf_upload';
            } else {
                wp_die('File Upload Error: ' . $movefile['error']);
            }
        }

        $resume->file_path = $file_path;
        $resume->file_type = $file_type;
        $resume->is_primary = 1; 
        $resume->status = 'active';
        
        // ==========================================
        // DYNAMIC JSON ARRAY HANDLING
        // ==========================================
        
        $experience_array = [];
        if (isset($_POST['experience']) && is_array($_POST['experience'])) {
            foreach ($_POST['experience'] as $exp) {
                if (!empty($exp['job_title']) && !empty($exp['company'])) {
                    $start_date = sanitize_text_field($exp['start_month'] ?? '') . ' ' . sanitize_text_field($exp['start_year'] ?? '');
                    if (isset($exp['current']) && $exp['current'] === 'on') {
                        $end_date = 'Present';
                    } else {
                        $end_date = sanitize_text_field($exp['end_month'] ?? '') . ' ' . sanitize_text_field($exp['end_year'] ?? '');
                    }
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
        $resume->experience_data = wp_json_encode($experience_array);

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
        $resume->education_data = wp_json_encode($education_array);

        $raw_skills = sanitize_text_field($_POST['skills'] ?? '');
        $skills_array = array_filter(array_map('trim', explode(',', $raw_skills)));
        $resume->skills_data = wp_json_encode(array_values($skills_array));

        // Create in Database
        $service = new ResumeService();
        $resumeId = $service->create($resume);

        if ($resumeId) {
            wp_redirect(home_url('/candidate-dashboard/?resume_created=1'));
            exit;
        } else {
            wp_redirect(add_query_arg('resume_error', '1', wp_get_referer()));
            exit;
        }
    }
}