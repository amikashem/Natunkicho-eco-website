<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Shortcodes;

use NKRecruitment\Employer\Models\Company;
use NKRecruitment\Employer\Services\CompanyService;

if (!defined('ABSPATH')) {
    exit;
}

class CreateCompanyShortcode
{
    public function register(): void
    {
        add_shortcode('nk_create_company', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleSubmission']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function enqueueScripts(): void
    {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'nk_create_company')) {
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
        }
    }

    public function render(): string
    {
        if (!is_user_logged_in() || !in_array('nkrp_employer', (array) wp_get_current_user()->roles)) {
            return '<div style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px;"><strong>Access Denied:</strong> You must be logged in as an Employer to create a company profile.</div>';
        }

        // Fetch Global Countries just like we did for the Jobs!
        $raw_countries = get_option('nkrp_global_countries', "United States\nUnited Kingdom\nCanada\nAustralia");
        $countries_array = array_filter(array_map('trim', explode("\n", $raw_countries)));

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-company-create.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px;"><strong>Error:</strong> The frontend-company-create.php view is missing.</div>';
        }
        
        return ob_get_clean();
    }

    public function handleSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nkrp_create_company_submit'])) return;
        if (!isset($_POST['nkrp_create_company_nonce']) || !wp_verify_nonce($_POST['nkrp_create_company_nonce'], 'nkrp_create_company_action')) wp_die('Security check failed.');
        if (!is_user_logged_in() || !in_array('nkrp_employer', (array) wp_get_current_user()->roles)) wp_die('Unauthorized action.');

        $company = new Company();
        
        // 1:1 Mapping with your Company.php Model
        $company->user_id      = get_current_user_id();
        $company->company_name = sanitize_text_field($_POST['company_name'] ?? '');
        $company->company_slug = sanitize_title($company->company_name); // Generates SEO URL
        
        $company->company_email= sanitize_email($_POST['company_email'] ?? '');
        $company->phone        = sanitize_text_field($_POST['phone'] ?? '');
        $company->website      = esc_url_raw($_POST['website'] ?? '');
        
        $company->industry     = sanitize_text_field($_POST['industry'] ?? '');
        $company->company_size = sanitize_text_field($_POST['company_size'] ?? '');
        $company->founded_year = !empty($_POST['founded_year']) ? (int) $_POST['founded_year'] : null;
        
        $company->country      = sanitize_text_field($_POST['country'] ?? '');
        $company->city         = sanitize_text_field($_POST['city'] ?? '');
        $company->address      = sanitize_text_field($_POST['address'] ?? '');
        
        $company->description  = wp_kses_post($_POST['description'] ?? '');
        
        $company->status       = 'active';
        $company->verified     = 0;

        $service = new CompanyService();
        $companyId = $service->create($company);

        if ($companyId) {
            wp_redirect(home_url('/employer-dashboard/?company_created=1'));
            exit;
        } else {
            wp_redirect(add_query_arg('company_error', '1', wp_get_referer()));
            exit;
        }
    }
}