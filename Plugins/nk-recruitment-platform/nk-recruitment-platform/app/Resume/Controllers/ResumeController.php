<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Controllers;

use NKRecruitment\Resume\Models\Resume;
use NKRecruitment\Resume\Services\ResumeService;

if (!defined('ABSPATH')) {
    exit;
}

class ResumeController
{
    private ResumeService $service;

    public function __construct()
    {
        $this->service = new ResumeService();
    }

    // Helper: Safely fetch Candidates so we can link Resumes to them
    private function getCandidates(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_candidates';
        $suppress = $wpdb->suppress_errors();
        
        $candidates = $wpdb->get_results("SELECT id, first_name, last_name, email FROM {$table} ORDER BY first_name ASC");
        
        $wpdb->suppress_errors($suppress);
        return $candidates ?: [];
    }

    // =====================================================
    // SECTION 1: Resume List
    // =====================================================

    public function resumeList(): void
    {
        // --- BULK ACTION LISTENER ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resume_ids'])) {
            check_admin_referer('bulk-resumes', 'bulk_resumes_nonce');
            
            $action = sanitize_text_field($_POST['action'] ?? '-1');
            $resume_ids = array_map('intval', $_POST['resume_ids']);

            if (in_array($action, ['active', 'hidden', 'draft'])) {
                $this->service->bulkUpdateStatus($resume_ids, $action);
                wp_redirect(admin_url('admin.php?page=nkrp-resumes&msg=updated'));
                exit;
            } elseif ($action === 'trash') {
                $this->service->bulkDelete($resume_ids);
                wp_redirect(admin_url('admin.php?page=nkrp-resumes&msg=deleted'));
                exit;
            }
        }

        // --- NORMAL LIST RENDERING ---
        $search = sanitize_text_field($_GET['s'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $paged  = max(1, (int) ($_GET['paged'] ?? 1));
        
        $limit  = 15;
        $offset = ($paged - 1) * $limit;

        $args = ['search' => $search, 'status' => $status, 'limit' => $limit, 'offset' => $offset, 'orderby' => 'id DESC'];

        $resumes     = $this->service->getResumes($args);
        $total_items = $this->service->countResumes($args);
        $total_pages = ceil($total_items / $limit);

        $count_all    = $this->service->countResumes(['search' => $search]);
        $count_active = $this->service->countResumes(['search' => $search, 'status' => 'active']);
        $count_hidden = $this->service->countResumes(['search' => $search, 'status' => 'hidden']);
        $count_draft  = $this->service->countResumes(['search' => $search, 'status' => 'draft']);

        require NKRP_PLUGIN_PATH . 'app/Resume/Views/resume-list.php';
    }

    // =====================================================
    // SECTION 2: Create Resume
    // =====================================================

    public function resumeCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_resume');

            $resume = new Resume();
            
            $resume->candidate_id   = (int) ($_POST['candidate_id'] ?? 0);
            $resume->user_id        = get_current_user_id(); // The admin/user creating it
            $resume->resume_title   = sanitize_text_field($_POST['resume_title'] ?? 'My Professional Resume');
            $resume->objective      = wp_kses_post($_POST['objective'] ?? '');
            
            // For MVP Phase 1, we capture these complex fields as raw text/json strings
            // The CV Builder module (Phase 2) will write proper JSON arrays here
            $resume->education_data      = wp_unslash($_POST['education_data'] ?? '[]');
            $resume->experience_data     = wp_unslash($_POST['experience_data'] ?? '[]');
            $resume->skills_data         = wp_unslash($_POST['skills_data'] ?? '[]');
            
            $resume->status         = sanitize_text_field($_POST['status'] ?? 'active');
            $resume->is_primary     = isset($_POST['is_primary']) ? 1 : 0;

            $this->service->create($resume);
            wp_redirect(admin_url('admin.php?page=nkrp-resumes&msg=created'));
            exit;
        }

        $candidates = $this->getCandidates();
        require NKRP_PLUGIN_PATH . 'app/Resume/Views/resume-create.php';
    }

    // =====================================================
    // SECTION 3: Edit Resume
    // =====================================================

    public function resumeEdit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) wp_die(__('Invalid Resume ID.', 'nk-recruitment'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_resume');
            
            $resume = new Resume();
            $resume->id             = $id;
            $resume->candidate_id   = (int) ($_POST['candidate_id'] ?? 0);
            $resume->resume_title   = sanitize_text_field($_POST['resume_title'] ?? '');
            $resume->objective      = wp_kses_post($_POST['objective'] ?? '');
            
            $resume->education_data      = wp_unslash($_POST['education_data'] ?? '[]');
            $resume->experience_data     = wp_unslash($_POST['experience_data'] ?? '[]');
            $resume->skills_data         = wp_unslash($_POST['skills_data'] ?? '[]');
            
            $resume->status         = sanitize_text_field($_POST['status'] ?? 'active');
            $resume->is_primary     = isset($_POST['is_primary']) ? 1 : 0;

            $this->service->update($resume);
            wp_redirect(admin_url('admin.php?page=nkrp-resumes&msg=updated'));
            exit;
        }

        $resume = $this->service->find($id);
        if (!$resume) wp_die(__('Resume not found.', 'nk-recruitment'));

        $candidates = $this->getCandidates();
        require NKRP_PLUGIN_PATH . 'app/Resume/Views/resume-edit.php';
    }

    // =====================================================
    // SECTION 4: Delete Resume
    // =====================================================

    public function resumeDelete(): void
    {
        if (isset($_GET['id']) && current_user_can('manage_options')) {
            $id = (int) $_GET['id'];
            $this->service->delete($id);
            wp_redirect(admin_url('admin.php?page=nkrp-resumes&msg=deleted'));
            exit;
        }
    }
    // =====================================================
    // SECTION 5: Frontend Template Renderer
    // =====================================================

    public function registerShortcodes(): void
    {
        add_shortcode('nk_resume', [$this, 'renderFrontendResume']);
    }

   public function renderFrontendResume($atts): string
    {
        // Fetch the global default template from our new settings!
        $global_default = get_option('nkrp_resume_default_template', 'default');

        // Add 'template' to our accepted shortcode attributes
        $atts = shortcode_atts([
            'id'       => 0,
            'template' => $global_default 
        ], $atts, 'nk_resume');
        
        $resume_id = (int) $atts['id'];

        if ($resume_id <= 0) {
            return '<p>No Resume ID provided.</p>';
        }

        $resume = $this->service->find($resume_id);
        if (!$resume || $resume->status !== 'active') {
            return '<p>Resume not found or is currently private.</p>';
        }

        global $wpdb;
        $candidate = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nkrp_candidates WHERE id = %d", $resume->candidate_id));

        if (!$candidate) {
            return '<p>Candidate data missing.</p>';
        }

        // SECURITY WHITELIST
        $allowed_templates = ['default', 'modern', 'professional', 'executive'];
        $template_name = in_array($atts['template'], $allowed_templates) ? $atts['template'] : 'default';

        // Buffer the dynamically selected template
        ob_start();
        $template_path = NKRP_PLUGIN_PATH . "templates/resume/{$template_name}.php";
        
        if (file_exists($template_path)) {
            require $template_path;
        } else {
            echo '<p>Template file missing: ' . esc_html($template_name) . '.php</p>';
        }
        
        return ob_get_clean();
    }

// =====================================================
    // SECTION 5: Resume Settings & Guide
    // =====================================================

    public function resumeSettings(): void
    {
        // 1. Process Form Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_resume_settings');

            $default_template = sanitize_text_field($_POST['default_template'] ?? 'default');
            $allow_pdf_export = isset($_POST['allow_pdf_export']) ? 'yes' : 'no';

            // Save settings globally
            update_option('nkrp_resume_default_template', $default_template);
            update_option('nkrp_resume_allow_pdf_export', $allow_pdf_export);

            wp_redirect(admin_url('admin.php?page=nkrp-resume-settings&msg=updated'));
            exit;
        }

        // 2. Fetch Current Settings
        $default_template = get_option('nkrp_resume_default_template', 'default');
        $allow_pdf_export = get_option('nkrp_resume_allow_pdf_export', 'no');

        // 3. Load the View
        require NKRP_PLUGIN_PATH . 'app/Resume/Views/resume-settings.php';
    }
}