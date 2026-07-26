<?php

declare(strict_types=1);

namespace NKRecruitment\Search\Controllers;

use NKRecruitment\Search\Services\SearchService;

if (!defined('ABSPATH')) {
    exit;
}

class SearchController
{
    private SearchService $service;

    public function __construct()
    {
        $this->service = new SearchService();
    }

    /**
     * Register the AJAX hooks for the Smart Search so WordPress
     * knows how to handle the JS requests from search-form.php
     */
    public function register(): void
    {
        // Jobs Search AJAX
        add_action('wp_ajax_nkrp_search_jobs', [$this, 'ajaxSearchJobs']);
        add_action('wp_ajax_nopriv_nkrp_search_jobs', [$this, 'ajaxSearchJobs']);

        // Companies Search AJAX
        add_action('wp_ajax_nkrp_search_companies', [$this, 'ajaxSearchCompanies']);
        add_action('wp_ajax_nopriv_nkrp_search_companies', [$this, 'ajaxSearchCompanies']);

        // Candidates Search AJAX
        add_action('wp_ajax_nkrp_search_candidates', [$this, 'ajaxSearchCandidates']);
        add_action('wp_ajax_nopriv_nkrp_search_candidates', [$this, 'ajaxSearchCandidates']);
    }

    public function ajaxSearchJobs(): void
    {
        $results = $this->service->searchJobs($_GET);
        wp_send_json($results);
    }

    public function ajaxSearchCompanies(): void
    {
        $results = $this->service->searchCompanies($_GET);
        wp_send_json($results);
    }

    public function ajaxSearchCandidates(): void
    {
        $results = $this->service->searchCandidates($_GET);
        wp_send_json($results);
    }

    // =====================================================
    // ADMIN DASHBOARD VIEW
    // =====================================================
    public function searchSettings(): void
    {
        // Require the view file for the Admin Dashboard
        if (function_exists('nkrp_safe_render_view')) {
            nkrp_safe_render_view(NKRP_PLUGIN_PATH . 'app/Search/Views/search-settings.php');
        } else {
            require NKRP_PLUGIN_PATH . 'app/Search/Views/search-settings.php';
        }
    }
}