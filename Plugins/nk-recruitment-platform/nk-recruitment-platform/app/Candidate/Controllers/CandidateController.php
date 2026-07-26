<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate\Controllers;

use NKRecruitment\Candidate\Models\Candidate;
use NKRecruitment\Candidate\Services\CandidateService;

if (!defined('ABSPATH')) {
    exit;
}

class CandidateController
{
    private CandidateService $service;

    public function __construct()
    {
        $this->service = new CandidateService();
    }

    // =====================================================
    // SECTION 1: Candidate List
    // =====================================================

    public function candidateList(): void
    {
        // --- BULK ACTION LISTENER ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['candidate_ids'])) {
            check_admin_referer('bulk-candidates', 'bulk_candidates_nonce');
            
            $action = sanitize_text_field($_POST['action'] ?? '-1');
            $candidate_ids = array_map('intval', $_POST['candidate_ids']);

            if (in_array($action, ['active', 'inactive', 'hired'])) {
                $this->service->bulkUpdateStatus($candidate_ids, $action);
                wp_redirect(admin_url('admin.php?page=nkrp-candidates&msg=updated'));
                exit;
            } elseif ($action === 'trash') {
                $this->service->bulkDelete($candidate_ids);
                wp_redirect(admin_url('admin.php?page=nkrp-candidates&msg=deleted'));
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

        $candidates  = $this->service->getCandidates($args);
        $total_items = $this->service->countCandidates($args);
        $total_pages = ceil($total_items / $limit);

        $count_all      = $this->service->countCandidates(['search' => $search]);
        $count_active   = $this->service->countCandidates(['search' => $search, 'status' => 'active']);
        $count_inactive = $this->service->countCandidates(['search' => $search, 'status' => 'inactive']);
        $count_hired    = $this->service->countCandidates(['search' => $search, 'status' => 'hired']);

        require NKRP_PLUGIN_PATH . 'app/Candidate/Views/candidate-list.php';
    }

    // =====================================================
    // SECTION 2: Create Candidate
    // =====================================================

    public function candidateCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_candidate');

            $candidate = new Candidate();
            
            // Core Identity
            $candidate->user_id            = get_current_user_id(); // Or allow manual override
            $candidate->first_name         = sanitize_text_field($_POST['first_name'] ?? '');
            $candidate->last_name          = sanitize_text_field($_POST['last_name'] ?? '');
            $candidate->email              = sanitize_email($_POST['email'] ?? '');
            $candidate->phone              = sanitize_text_field($_POST['phone'] ?? '');
            $candidate->professional_title = sanitize_text_field($_POST['professional_title'] ?? '');
            
            // Location & Demographics
            $candidate->location_city      = sanitize_text_field($_POST['location_city'] ?? '');
            $candidate->location_country   = sanitize_text_field($_POST['location_country'] ?? '');
            $candidate->gender             = sanitize_text_field($_POST['gender'] ?? '');
            $candidate->nationality        = sanitize_text_field($_POST['nationality'] ?? '');
            $candidate->date_of_birth      = !empty($_POST['date_of_birth']) ? sanitize_text_field($_POST['date_of_birth']) : null;
            
            // Professional Details
            $candidate->current_salary     = (float) ($_POST['current_salary'] ?? 0);
            $candidate->expected_salary    = (float) ($_POST['expected_salary'] ?? 0);
            $candidate->salary_currency    = sanitize_text_field($_POST['salary_currency'] ?? 'USD');
            $candidate->experience_years   = (int) ($_POST['experience_years'] ?? 0);
            $candidate->education_level    = sanitize_text_field($_POST['education_level'] ?? '');
            $candidate->availability       = sanitize_text_field($_POST['availability'] ?? '');
            $candidate->bio                = wp_kses_post($_POST['bio'] ?? '');
            $candidate->skills             = sanitize_textarea_field($_POST['skills'] ?? '');
            $candidate->languages          = sanitize_textarea_field($_POST['languages'] ?? '');
            
            // Links & System
            $candidate->linkedin_url       = esc_url_raw($_POST['linkedin_url'] ?? '');
            $candidate->portfolio_url      = esc_url_raw($_POST['portfolio_url'] ?? '');
            $candidate->status             = sanitize_text_field($_POST['status'] ?? 'active');
            $candidate->is_featured        = isset($_POST['is_featured']) ? 1 : 0;

            $this->service->create($candidate);
            wp_redirect(admin_url('admin.php?page=nkrp-candidates&msg=created'));
            exit;
        }

        require NKRP_PLUGIN_PATH . 'app/Candidate/Views/candidate-create.php';
    }

    // =====================================================
    // SECTION 3: Edit Candidate
    // =====================================================

    public function candidateEdit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) wp_die(__('Invalid Candidate ID.', 'nk-recruitment'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_candidate');
            
            $candidate = new Candidate();
            $candidate->id                 = $id;
            $candidate->first_name         = sanitize_text_field($_POST['first_name'] ?? '');
            $candidate->last_name          = sanitize_text_field($_POST['last_name'] ?? '');
            $candidate->email              = sanitize_email($_POST['email'] ?? '');
            $candidate->phone              = sanitize_text_field($_POST['phone'] ?? '');
            $candidate->professional_title = sanitize_text_field($_POST['professional_title'] ?? '');
            $candidate->location_city      = sanitize_text_field($_POST['location_city'] ?? '');
            $candidate->location_country   = sanitize_text_field($_POST['location_country'] ?? '');
            $candidate->gender             = sanitize_text_field($_POST['gender'] ?? '');
            $candidate->nationality        = sanitize_text_field($_POST['nationality'] ?? '');
            $candidate->date_of_birth      = !empty($_POST['date_of_birth']) ? sanitize_text_field($_POST['date_of_birth']) : null;
            $candidate->current_salary     = (float) ($_POST['current_salary'] ?? 0);
            $candidate->expected_salary    = (float) ($_POST['expected_salary'] ?? 0);
            $candidate->salary_currency    = sanitize_text_field($_POST['salary_currency'] ?? 'USD');
            $candidate->experience_years   = (int) ($_POST['experience_years'] ?? 0);
            $candidate->education_level    = sanitize_text_field($_POST['education_level'] ?? '');
            $candidate->availability       = sanitize_text_field($_POST['availability'] ?? '');
            $candidate->bio                = wp_kses_post($_POST['bio'] ?? '');
            $candidate->skills             = sanitize_textarea_field($_POST['skills'] ?? '');
            $candidate->languages          = sanitize_textarea_field($_POST['languages'] ?? '');
            $candidate->linkedin_url       = esc_url_raw($_POST['linkedin_url'] ?? '');
            $candidate->portfolio_url      = esc_url_raw($_POST['portfolio_url'] ?? '');
            $candidate->status             = sanitize_text_field($_POST['status'] ?? 'active');
            $candidate->is_featured        = isset($_POST['is_featured']) ? 1 : 0;

            $this->service->update($candidate);
            wp_redirect(admin_url('admin.php?page=nkrp-candidates&msg=updated'));
            exit;
        }

        $candidate = $this->service->find($id);
        if (!$candidate) wp_die(__('Candidate not found.', 'nk-recruitment'));

        require NKRP_PLUGIN_PATH . 'app/Candidate/Views/candidate-edit.php';
    }

    // =====================================================
    // SECTION 4: Delete Candidate
    // =====================================================

    public function candidateDelete(): void
    {
        if (isset($_GET['id']) && current_user_can('manage_options')) {
            $id = (int) $_GET['id'];
            $this->service->delete($id);
            wp_redirect(admin_url('admin.php?page=nkrp-candidates&msg=deleted'));
            exit;
        }
    }
}