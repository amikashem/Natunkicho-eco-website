<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * THE "NUCLEAR" SEO ROUTER (With Yoast/RankMath Title Overrides)
 * =========================================================================
 */

add_action('template_redirect', 'nk_nuclear_salary_router', 1);
function nk_nuclear_salary_router() {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $path);

    // --- Route 1: The Hub (/salaries) ---
    if (isset($segments[0]) && strtolower($segments[0]) === 'salaries') {
        
        $template = get_stylesheet_directory() . '/template-parts/salaries/archive-salary.php';
        if (!file_exists($template)) wp_die('File missing: ' . $template);

        // Force SEO Plugins to show the correct Browser Tab Title
        $seo_title = 'Hospitality Salary Intelligence Center - NatunKicho';
        $force_title = function() use ($seo_title) { return $seo_title; };
        add_filter('pre_get_document_title', $force_title, 9999);
        add_filter('wpseo_title', $force_title, 9999); // Yoast
        add_filter('rank_math/frontend/title', $force_title, 9999); // RankMath
        add_filter('aioseo_title', $force_title, 9999); // All in One SEO

        global $wp_query;
        $wp_query->is_404 = false;
        status_header(200);
        include($template);
        exit;
    }

    // --- Route 2: Single Report (/salary/position/country) ---
    if (isset($segments[0]) && strtolower($segments[0]) === 'salary' && !empty($segments[1]) && !empty($segments[2])) {
        
        $position = ucwords(str_replace('-', ' ', $segments[1]));
        $country = ucwords(str_replace('-', ' ', $segments[2]));
        
        set_query_var('salary_position', $segments[1]);
        set_query_var('salary_country', $segments[2]);
        
        $template = get_stylesheet_directory() . '/template-parts/salaries/single-salary.php';
        if (!file_exists($template)) wp_die('File missing: ' . $template);

        // Force SEO Plugins to show the correct Browser Tab Title
        $seo_title = "{$position} Salary in {$country} - Market Report";
        $force_title = function() use ($seo_title) { return $seo_title; };
        add_filter('pre_get_document_title', $force_title, 9999);
        add_filter('wpseo_title', $force_title, 9999);
        add_filter('rank_math/frontend/title', $force_title, 9999);
        add_filter('aioseo_title', $force_title, 9999);

        global $wp_query;
        $wp_query->is_404 = false;
        status_header(200);
        include($template);
        exit;
    }
}
// 2. Dynamic SEO Titles for Google
add_filter('document_title_parts', 'nk_salary_dynamic_seo_titles', 999);
function nk_salary_dynamic_seo_titles($title) {
    if (get_query_var('is_salary_hub')) {
        $title['title'] = 'Hospitality Salary Intelligence Center';
    } 
    elseif (get_query_var('salary_position')) {
        $position = ucwords(str_replace('-', ' ', get_query_var('salary_position')));
        $country = ucwords(str_replace('-', ' ', get_query_var('salary_country')));
        $title['title'] = "{$position} Salary in {$country} - Market Report";
    }
    return $title;
}