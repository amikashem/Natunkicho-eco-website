<?php

declare(strict_types=1);

namespace NKRecruitment\ATS;

use NKRecruitment\Core\ServiceProvider;

if (!defined('ABSPATH')) {
    exit;
}

class ApplicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'loadAssets']);
    }

    public function loadAssets(string $hook): void
    {
        if (!isset($_GET['page'])) {
            return;
        }

        // Only load ATS assets on ATS pages
        $allowedPages = [
            'nkrp-applications',
            'nkrp-application-create',
            'nkrp-application-edit',
        ];

        if (in_array($_GET['page'], $allowedPages, true)) {
            // Future UI upgrades (Drag and Drop Kanban boards, etc.) will load their JS here!
            // wp_enqueue_style('nkrp-ats-style', NKRP_PLUGIN_URL . 'assets/css/ats.css');
        }
    }
}