<?php

declare(strict_types=1);

namespace NKRecruitment\Search;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Search\Controllers\SearchController;

if (!defined('ABSPATH')) {
    exit;
}

class SearchServiceProvider extends ServiceProvider
{
   public function register(): void
    {
        $controller = new SearchController();

        // Register AJAX Endpoints
        add_action('wp_ajax_nkrp_search_jobs', [$controller, 'ajaxSearchJobs']);
        add_action('wp_ajax_nopriv_nkrp_search_jobs', [$controller, 'ajaxSearchJobs']);
        add_action('wp_ajax_nkrp_search_companies', [$controller, 'ajaxSearchCompanies']);
        add_action('wp_ajax_nopriv_nkrp_search_companies', [$controller, 'ajaxSearchCompanies']);
        add_action('wp_ajax_nkrp_search_candidates', [$controller, 'ajaxSearchCandidates']);
        add_action('wp_ajax_nopriv_nkrp_search_candidates', [$controller, 'ajaxSearchCandidates']);

        // Register the Shortcode
        (new \NKRecruitment\Search\Shortcodes\SearchShortcode())->register();
    }
}