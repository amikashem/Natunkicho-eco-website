<?php

declare(strict_types=1);

namespace NKRecruitment\Analytics;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Analytics\Controllers\TrackingController;

if (!defined('ABSPATH')) {
    exit;
}

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $trackingController = new TrackingController();

        // Register AJAX endpoints for frontend tracking (views, clicks, applies)
        add_action('wp_ajax_nkrp_track_view', [$trackingController, 'ajaxTrackView']);
        add_action('wp_ajax_nopriv_nkrp_track_view', [$trackingController, 'ajaxTrackView']);
    }
}