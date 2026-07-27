<?php

declare(strict_types=1);

namespace NKRecruitment\Admin;

use NKRecruitment\Jobs\Controllers\JobController;
use NKRecruitment\Candidate\Controllers\CandidateController; 
use NKRecruitment\Resume\Controllers\ResumeController;
use NKRecruitment\Employer\Controllers\EmployerController;
use NKRecruitment\ATS\Controllers\ApplicationController;
use NKRecruitment\Search\Controllers\SearchController;
use NKRecruitment\AI\Controllers\AIController;
use NKRecruitment\Analytics\Controllers\AnalyticsDashboardController;

if (!defined('ABSPATH')) {
    exit;
}

class MenuManager
{
    public function register(): void
    {
        // Priority 20 ensures this loads correctly after other initializations
        add_action('admin_menu', [$this, 'menus'], 20);
    }

    public function menus(): void
    {
        $jobController         = new JobController();
        $candidateController   = new CandidateController();
        $resumeController      = new ResumeController();
        $employerController    = new EmployerController();
        $applicationController = new ApplicationController();
        $searchController      = new SearchController();
        $aiController          = new AIController();
        $analyticsController   = new AnalyticsDashboardController();

        // =========================================================
        // 1. MAIN PARENT MENU
        // =========================================================
        add_menu_page(
            __('NK Recruitment', 'nk-recruitment'),
            __('NK Recruitment', 'nk-recruitment'),
            'manage_options',
            'nk-recruitment',
            [$this, 'dashboard'],
            'dashicons-businessperson',
            26
        );

        // =========================================================
        // 2. VISIBLE SIDEBAR MODULES
        // =========================================================
        add_submenu_page('nk-recruitment', __('Dashboard', 'nk-recruitment'), __('Dashboard', 'nk-recruitment'), 'manage_options', 'nk-recruitment', [$this, 'dashboard']);
        add_submenu_page('nk-recruitment', __('Companies', 'nk-recruitment'), __('Companies', 'nk-recruitment'), 'manage_options', 'nkrp-companies', [$employerController, 'companyList']);
        add_submenu_page('nk-recruitment', __('Jobs', 'nk-recruitment'), __('Jobs', 'nk-recruitment'), 'manage_options', 'nkrp-jobs', [$jobController, 'jobList']);
        add_submenu_page('nk-recruitment', __('Candidates', 'nk-recruitment'), __('Candidates', 'nk-recruitment'), 'manage_options', 'nkrp-candidates', [$candidateController, 'candidateList']);
        add_submenu_page('nk-recruitment', __('Resumes', 'nk-recruitment'), __('Resumes', 'nk-recruitment'), 'manage_options', 'nkrp-resumes', [$resumeController, 'resumeList']);
        add_submenu_page('nk-recruitment', __('Applications', 'nk-recruitment'), __('Applications (ATS)', 'nk-recruitment'), 'manage_options', 'nkrp-applications', [$applicationController, 'applicationList']);
        add_submenu_page('nk-recruitment', __('AI Core', 'nk-recruitment'), __('AI Core', 'nk-recruitment'), 'manage_options', 'nkrp-ai-core', [$aiController, 'dashboard']);
        add_submenu_page('nk-recruitment', __('Analytics', 'nk-recruitment'), __('Analytics', 'nk-recruitment'), 'manage_options', 'nkrp-analytics', [$analyticsController, 'renderDashboard']);
        
        // UNHIDDEN: Brought the Settings back to the sidebar for easy access!
        add_submenu_page('nk-recruitment', __('Job Settings', 'nk-recruitment'), __('Job Settings', 'nk-recruitment'), 'manage_options', 'nkrp-settings', [$jobController, 'jobSettings']);
        add_submenu_page('nk-recruitment', __('Resume Settings', 'nk-recruitment'), __('Resume Settings', 'nk-recruitment'), 'manage_options', 'nkrp-resume-settings', [$resumeController, 'resumeSettings']);
        add_submenu_page('nk-recruitment', __('Search Settings', 'nk-recruitment'), __('Search Settings', 'nk-recruitment'), 'manage_options', 'nkrp-search-settings', [$searchController, 'searchSettings']);

        // =========================================================
        // 3. HIDDEN ACTION ROUTES (Edit/Delete Pages - MUST REMAIN HIDDEN)
        // =========================================================
        
        // --- Companies ---
        add_submenu_page(null, __('Add Company', 'nk-recruitment'), __('Add Company', 'nk-recruitment'), 'manage_options', 'nkrp-company-create', [$employerController, 'companyCreate']);
        add_submenu_page(null, __('Edit Company', 'nk-recruitment'), __('Edit Company', 'nk-recruitment'), 'manage_options', 'nkrp-company-edit', [$employerController, 'companyEdit']);

        // --- Jobs ---
        add_submenu_page(null, __('Add Job', 'nk-recruitment'), __('Add Job', 'nk-recruitment'), 'manage_options', 'nkrp-job-create', [$jobController, 'jobCreate']);
        add_submenu_page(null, __('Edit Job', 'nk-recruitment'), __('Edit Job', 'nk-recruitment'), 'manage_options', 'nkrp-job-edit', [$jobController, 'jobEdit']);
        add_submenu_page(null, __('Delete Job', 'nk-recruitment'), __('Delete Job', 'nk-recruitment'), 'manage_options', 'nkrp-job-delete', [$jobController, 'jobDelete']);

        // --- Candidates ---
        add_submenu_page(null, __('Add Candidate', 'nk-recruitment'), __('Add Candidate', 'nk-recruitment'), 'manage_options', 'nkrp-candidate-create', [$candidateController, 'candidateCreate']);
        add_submenu_page(null, __('Edit Candidate', 'nk-recruitment'), __('Edit Candidate', 'nk-recruitment'), 'manage_options', 'nkrp-candidate-edit', [$candidateController, 'candidateEdit']);
        add_submenu_page(null, __('Delete Candidate', 'nk-recruitment'), __('Delete Candidate', 'nk-recruitment'), 'manage_options', 'nkrp-candidate-delete', [$candidateController, 'candidateDelete']);

        // --- Resumes ---
        add_submenu_page(null, __('Add Resume', 'nk-recruitment'), __('Add Resume', 'nk-recruitment'), 'manage_options', 'nkrp-resume-create', [$resumeController, 'resumeCreate']);
        add_submenu_page(null, __('Edit Resume', 'nk-recruitment'), __('Edit Resume', 'nk-recruitment'), 'manage_options', 'nkrp-resume-edit', [$resumeController, 'resumeEdit']);
        add_submenu_page(null, __('Delete Resume', 'nk-recruitment'), __('Delete Resume', 'nk-recruitment'), 'manage_options', 'nkrp-resume-delete', [$resumeController, 'resumeDelete']);

        // --- Applications (ATS) ---
        add_submenu_page(null, __('Add Application', 'nk-recruitment'), __('Add Application', 'nk-recruitment'), 'manage_options', 'nkrp-application-create', [$applicationController, 'applicationCreate']);
        add_submenu_page(null, __('Review Application', 'nk-recruitment'), __('Review Application', 'nk-recruitment'), 'manage_options', 'nkrp-application-edit', [$applicationController, 'applicationEdit']);
        add_submenu_page(null, __('Delete Application', 'nk-recruitment'), __('Delete Application', 'nk-recruitment'), 'manage_options', 'nkrp-application-delete', [$applicationController, 'applicationDelete']);
    }

    public function dashboard(): void
    {
        require NKRP_PLUGIN_PATH . 'app/Admin/Views/dashboard.php';
    }
}