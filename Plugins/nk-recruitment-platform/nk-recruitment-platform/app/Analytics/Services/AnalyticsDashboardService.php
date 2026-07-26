<?php

declare(strict_types=1);

namespace NKRecruitment\Analytics\Services;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class AnalyticsDashboardService
{
    private \wpdb $db;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
    }

    /**
     * High-Performance Aggregation: Gets all stats in just a few queries.
     */
    public function getMasterStats(): array
    {
        // 1. Job Stats (One Query)
        $jobs_table = DatabaseManager::table('jobs');
        $job_stats = $this->db->get_row("SELECT 
            COUNT(id) as total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
            SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured
            FROM {$jobs_table}");

        // 2. Application Stats (One Query)
        $apps_table = DatabaseManager::table('applications');
        $app_stats = $this->db->get_row("SELECT 
            COUNT(id) as total,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
            SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
            SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview,
            SUM(CASE WHEN status = 'offered' THEN 1 ELSE 0 END) as offered,
            SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM {$apps_table}");

        // 3. AI Stats (One Query)
        $ai_table = DatabaseManager::table('ai_logs');
        $ai_stats = $this->db->get_row("SELECT 
            COUNT(id) as total_requests,
            SUM(total_tokens) as total_tokens,
            SUM(estimated_cost) as total_cost
            FROM {$ai_table}");

        // MODERN PHP FIX: Use simple null checks (?:) instead of reset()
        return [
            'jobs' => $job_stats ?: (object)['total'=>0, 'published'=>0, 'draft'=>0, 'expired'=>0, 'featured'=>0],
            'apps' => $app_stats ?: (object)['total'=>0, 'pending'=>0, 'reviewed'=>0, 'shortlisted'=>0, 'interview'=>0, 'offered'=>0, 'hired'=>0, 'rejected'=>0],
            'ai'   => $ai_stats ?: (object)['total_requests'=>0, 'total_tokens'=>0, 'total_cost'=>0],
        ];
    }
}