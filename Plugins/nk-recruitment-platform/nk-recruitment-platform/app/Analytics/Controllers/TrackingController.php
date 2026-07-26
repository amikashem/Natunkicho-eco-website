<?php

declare(strict_types=1);

namespace NKRecruitment\Analytics\Controllers;

use NKRecruitment\Analytics\Services\TrackingService;

if (!defined('ABSPATH')) {
    exit;
}

class TrackingController
{
    private TrackingService $service;

    public function __construct()
    {
        $this->service = new TrackingService();
    }

    /**
     * Endpoint for frontend JavaScript to securely ping a view
     */
    public function ajaxTrackView(): void
    {
        // Basic security check
        if (!isset($_POST['entity_id'], $_POST['entity_type'], $_POST['event_type'])) {
            wp_send_json_error('Missing data');
        }

        $entity_id   = (int) $_POST['entity_id'];
        $entity_type = sanitize_text_field($_POST['entity_type']);
        $event_type  = sanitize_text_field($_POST['event_type']);

        $this->service->logEvent($event_type, $entity_type, $entity_id);

        wp_send_json_success();
    }
}