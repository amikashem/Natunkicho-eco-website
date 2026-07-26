<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

use NKRecruitment\Jobs\Repositories\JobRepository;
use NKRecruitment\Employer\Repositories\CompanyRepository; // Added Company Repository

if (!defined('ABSPATH')) {
    exit;
}

class Router
{
    public function register(): void
    {
        // 1. Add our custom URL routing rules
        add_action('init', [$this, 'addRewriteRules']);
        
        // 2. Register our custom variables safely
        add_filter('query_vars', [$this, 'addQueryVars']);
        
        // 3. Intercept the page load to show our custom templates
        add_filter('template_include', [$this, 'loadCustomTemplates']);
    }

    /**
     * Step 1: The Map
     */
    public function addRewriteRules(): void
    {
        // Job Routing
        add_rewrite_rule(
            '^job/([^/]*)/?',
            'index.php?nkrp_job_slug=$matches[1]',
            'top'
        );

        // Company Routing (NEW)
        add_rewrite_rule(
            '^company/([^/]*)/?',
            'index.php?nkrp_company_slug=$matches[1]',
            'top' 
        );
    }

    /**
     * Step 2: The Variables
     */
    public function addQueryVars(array $vars): array
    {
        $vars[] = 'nkrp_job_slug';
        $vars[] = 'nkrp_company_slug'; // NEW
        return $vars;
    }

    /**
     * Step 3: The Hijack (Template Interceptor)
     */
    public function loadCustomTemplates($template)
    {
        // --- A. Handle Job Routing ---
        $jobSlug = get_query_var('nkrp_job_slug');
        if (!empty($jobSlug)) {
            $repository = new JobRepository();
            $job = $repository->findBySlug(sanitize_title($jobSlug));

            if ($job) {
                global $nkrp_current_job;
                $nkrp_current_job = $job;
                $customTemplate = NKRP_PLUGIN_PATH . 'templates/public/job-details.php';
                if (file_exists($customTemplate)) return $customTemplate;
            }
            return $this->force404();
        }

        // --- B. Handle Company Routing (NEW) ---
        $companySlug = get_query_var('nkrp_company_slug');
        if (!empty($companySlug)) {
            $repository = new CompanyRepository();
            $company = $repository->findBySlug(sanitize_title($companySlug));

            if ($company) {
                global $nkrp_current_company;
                $nkrp_current_company = $company;
                $customTemplate = NKRP_PLUGIN_PATH . 'templates/public/company-profile.php';
                
                if (file_exists($customTemplate)) {
                    return $customTemplate; 
                }
            }
            return $this->force404();
        }

        // Let WordPress load normally if no custom routes match
        return $template;
    }

    /**
     * Helper to force a standard 404 page if a job/company isn't found
     */
    private function force404()
    {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        return get_query_template('404');
    }
}