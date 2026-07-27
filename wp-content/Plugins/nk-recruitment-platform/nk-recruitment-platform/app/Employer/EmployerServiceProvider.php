<?php

declare(strict_types=1);

namespace NKRecruitment\Employer;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Employer\Shortcodes\CompanyProfileShortcode;

if (!defined('ABSPATH')) {
    exit;
}

class EmployerServiceProvider extends ServiceProvider
{
   // =====================================================
    // SECTION 1: Register Module
    // =====================================================

    public function register(): void
    {
        // 1. Load Admin Assets (PRESERVED)
        add_action(
            'admin_enqueue_scripts',
            [$this, 'assets']
        );

        // 2. Register Employer Shortcodes
        (new \NKRecruitment\Employer\Shortcodes\EmployerDashboardShortcode())->register();
        
        // NEW 3. Register the Company Builder Shortcode
        (new \NKRecruitment\Employer\Shortcodes\CreateCompanyShortcode())->register();
        (new CompanyProfileShortcode())->register();
    }

    // =====================================================
    // SECTION 2: Load CSS / JS (PRESERVED)
    // =====================================================

    public function assets(string $hook): void
    {
        if (!isset($_GET['page'])) {
            return;
        }

        $allowedPages = [
            'nkrp-companies',
            'nkrp-company-create',
            'nkrp-company-edit',
        ];

        if (!in_array($_GET['page'], $allowedPages, true)) {
            return;
        }

        wp_enqueue_media();
        
        wp_enqueue_script(
            'nkrp-media-uploader',
            NKRP_PLUGIN_URL . 'app/Media/Assets/js/media-uploader.js',
            [
                'jquery',
                'media-editor',
                'media-models',
                'media-views'
            ],
            NKRP_VERSION,
            true
        );
    }
}