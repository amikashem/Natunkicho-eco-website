<?php

declare(strict_types=1);

namespace NKRecruitment\Analytics\Services;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class TrackingService
{
    private \wpdb $db;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
    }

    /**
     * Log a telemetry event (View, Click, Apply)
     */
    public function logEvent(string $event_type, string $entity_type, int $entity_id): void
    {
        $table = DatabaseManager::table('analytics_events');
        
        // Hash the IP address for GDPR compliance. We only need the hash to track "Unique Views"
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip_hash = hash('sha256', $ip . wp_salt());

        $this->db->insert($table, [
            'event_type'  => sanitize_text_field($event_type),
            'entity_type' => sanitize_text_field($entity_type),
            'entity_id'   => $entity_id,
            'user_id'     => get_current_user_id(), // Returns 0 for logged-out guests
            'ip_hash'     => $ip_hash,
            'created_at'  => current_time('mysql')
        ]);
    }

    /**
     * Fetch time-series data for SaaS Charts (e.g., Views over the last 7 days)
     */
    public function getChartData(int $job_id, int $days = 7): array
    {
        $table = DatabaseManager::table('analytics_events');
        
        // This query groups views by day so we can feed it straight into Chart.js
        $query = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(id) as total_views 
            FROM {$table} 
            WHERE event_type = 'job_view' 
              AND entity_type = 'job' 
              AND entity_id = %d 
              AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", $job_id, $days);

        return $this->db->get_results($query, ARRAY_A) ?: [];
    }
}