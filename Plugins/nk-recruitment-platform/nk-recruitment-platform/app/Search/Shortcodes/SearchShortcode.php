<?php

declare(strict_types=1);

namespace NKRecruitment\Search\Shortcodes;

use NKRecruitment\Search\Helpers\SearchHelper;
use NKRecruitment\Search\Services\SearchService;

if (!defined('ABSPATH')) {
    exit;
}

class SearchShortcode
{
    public function register(): void
    {
        add_shortcode('nk_search', [$this, 'render']);
    }

    public function render($atts): string
    {
        $atts = shortcode_atts([
            'type'  => 'jobs', // Options: 'jobs', 'companies', 'candidates'
            'limit' => 12
        ], $atts, 'nk_search');

        $search_type = sanitize_text_field($atts['type']);
        $limit = intval($atts['limit']);
        $allowed_types = ['jobs', 'companies', 'candidates'];
        
        if (!in_array($search_type, $allowed_types)) {
            return '<p>Invalid search type specified.</p>';
        }

        // =========================================================
        // CAPTURE URL FILTERS (Perfectly matched to your UI)
        // =========================================================
        $filters = [];
        
        if ($search_type === 'candidates') {
            if (!empty($_GET['role'])) $filters['role'] = sanitize_text_field($_GET['role']);
            if (!empty($_GET['skill'])) $filters['skill'] = sanitize_text_field($_GET['skill']);
            if (!empty($_GET['location'])) $filters['location'] = sanitize_text_field($_GET['location']);
        } else {
            if (!empty($_GET['keyword'])) $filters['keyword'] = sanitize_text_field($_GET['keyword']);
            if (!empty($_GET['location'])) $filters['location'] = sanitize_text_field($_GET['location']);
            if (!empty($_GET['category'])) $filters['category'] = sanitize_text_field($_GET['category']);
        }

        $searchService = new SearchService();
        $initial_results = [];
        
        try {
            if ($search_type === 'jobs') {
                $initial_results = $searchService->searchJobs($filters, $limit);
            } elseif ($search_type === 'companies') {
                $initial_results = $searchService->searchCompanies($filters, $limit);
            } elseif ($search_type === 'candidates') {
                $initial_results = $searchService->searchCandidates($filters, $limit);
            }
        } catch (\Exception $e) {
            error_log('NKRP Search Error: ' . $e->getMessage());
        }

        ob_start();
        
        echo '<div class="nkrp-search-container" data-search-type="' . esc_attr($search_type) . '">';
        
        require NKRP_PLUGIN_PATH . 'app/Search/Views/search-form.php';
        require NKRP_PLUGIN_PATH . 'app/Search/Views/search-results.php';
        
        echo '</div>'; 

        return ob_get_clean();
    }
}