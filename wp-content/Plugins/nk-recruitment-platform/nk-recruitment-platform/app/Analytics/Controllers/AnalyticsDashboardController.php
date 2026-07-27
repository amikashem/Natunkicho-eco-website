<?php

declare(strict_types=1);

namespace NKRecruitment\Analytics\Controllers;

use NKRecruitment\Analytics\Services\AnalyticsDashboardService;

if (!defined('ABSPATH')) {
    exit;
}

class AnalyticsDashboardController
{
    private AnalyticsDashboardService $service;

    public function __construct()
    {
        $this->service = new AnalyticsDashboardService();
    }

    public function renderDashboard(): void
    {
        // 1. Export Action Listener (Foundation)
        if (isset($_GET['export_format'])) {
            $this->handleExport(sanitize_text_field($_GET['export_format']));
        }

        // 2. Fetch all aggregated data
        $stats = $this->service->getMasterStats();

        // 3. Load the View
        require NKRP_PLUGIN_PATH . 'app/Analytics/Views/dashboard.php';
    }

    private function handleExport(string $format): void
    {
        // Future foundation for CSV/Excel/PDF generation
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="nk-analytics-export.csv"');
            echo "Metric,Value\nTotal Jobs,0\n"; // Mock implementation
            exit;
        }
    }
}