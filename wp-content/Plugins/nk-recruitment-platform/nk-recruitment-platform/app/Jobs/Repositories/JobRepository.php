<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Repositories;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Jobs\Models\Job;

if (!defined('ABSPATH')) {
    exit;
}

class JobRepository
{
    private \wpdb $db;
    private string $table;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->table = DatabaseManager::table('jobs');
    }

    // =====================================================
    // SECTION 1 : Create Job
    // =====================================================

    public function create(Job $job): int
    {
        $this->db->insert(
            $this->table,
            [
                'company_id'         => $job->company_id,
                'user_id'            => get_current_user_id(),
                'job_title'          => $job->title,
                'job_slug'           => $job->slug ?? $job->job_slug,
                'department'         => $job->department ?? '',
                'employment_type'    => $job->job_type ?? '',
                'vacancies'          => $job->vacancies,
                'experience_level'   => $job->experience ?? '',
                'education'          => $job->education ?? '',
                'salary_min'         => $job->salary_min,
                'salary_max'         => $job->salary_max,
                'salary_currency'    => $job->currency ?? 'USD',
                'country'            => $job->country ?? '',
                'city'               => $job->location ?? '',
                'deadline'           => $job->deadline ?? null,
                'description'        => $job->description ?? '',
                'requirements'       => $job->requirements ?? '',
                'responsibilities'   => $job->responsibilities ?? '',
                'benefits'           => $job->benefits ?? '',
                'external_apply_url' => $job->external_apply_url ?? '',
                'status'             => $job->status ?? 'draft',
                'featured'           => $job->featured ?? 0,
            ]
        );

        if ($this->db->last_error) {
            wp_die('<pre>' . esc_html($this->db->last_error) . '</pre>');
        }

        $inserted_id = (int) $this->db->insert_id;
        
        // NEW: Save the notification email as post meta associated with this specific Job ID
        if ($inserted_id > 0 && !empty($job->notification_email)) {
            update_post_meta($inserted_id, '_nkrp_notification_email', $job->notification_email);
        }

        return $inserted_id;
    }

    // =====================================================
    // SECTION 2 : Find Job
    // =====================================================

    public function find(int $id): ?object
    {
        $job = $this->db->get_row($this->db->prepare("SELECT * FROM {$this->table} WHERE id=%d", $id));
        
        if ($job) {
            $job->title = $job->job_title;
            $job->location = $job->city;
            $job->currency = $job->salary_currency;
            $job->job_type = $job->employment_type;
            
            // NEW: Retrieve Notification Email from Meta
            $job->notification_email = get_post_meta($id, '_nkrp_notification_email', true);
        }
        return $job;
    }

    // =====================================================
    // SECTION 3 : Update Job
    // =====================================================

    public function update(Job $job): bool
    {
        $updated = (bool) $this->db->update(
            $this->table,
            [
                'company_id'         => $job->company_id,
                'job_title'          => $job->title,
                'job_slug'           => $job->slug ?? $job->job_slug,
                'department'         => $job->department ?? '',
                'employment_type'    => $job->job_type ?? '',
                'vacancies'          => $job->vacancies,
                'salary_min'         => $job->salary_min,
                'salary_max'         => $job->salary_max,
                'salary_currency'    => $job->currency ?? 'USD',
                'country'            => $job->country ?? '',
                'city'               => $job->location ?? '',
                'deadline'           => $job->deadline ?? null,
                'description'        => $job->description ?? '',
                'requirements'       => $job->requirements ?? '',
                'responsibilities'   => $job->responsibilities ?? '',
                'benefits'           => $job->benefits ?? '',
                'external_apply_url' => $job->external_apply_url ?? '',
                'status'             => $job->status ?? 'draft',
                'featured'           => $job->featured ?? 0,
            ],
            ['id' => $job->id]
        );
        
        // NEW: Update Notification Email in Meta
        if (isset($job->notification_email)) {
            update_post_meta($job->id, '_nkrp_notification_email', $job->notification_email);
        }

        return $updated;
    }

    // =====================================================
    // SECTION 4 : Delete Job
    // =====================================================

    public function delete(int $id): bool
    {
        delete_post_meta($id, '_nkrp_notification_email');
        return (bool) $this->db->delete($this->table, ['id' => $id]);
    }

    // =====================================================
    // SECTION 5 : Get Jobs (List with Filters & Pagination)
    // =====================================================

    public function getJobs(array $args = []): array
    {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(job_title LIKE %s OR department LIKE %s OR city LIKE %s)';
            $search_term = '%' . $this->db->esc_like($args['search']) . '%';
            array_push($values, $search_term, $search_term, $search_term);
        }

        $whereSql = implode(' AND ', $where);
        
        $limit = (int) ($args['limit'] ?? 20);
        $offset = (int) ($args['offset'] ?? 0);
        $orderBy = sanitize_sql_orderby($args['orderby'] ?? 'id DESC');

        $sql = "SELECT * FROM {$this->table} WHERE {$whereSql} ORDER BY {$orderBy} LIMIT %d OFFSET %d";
        
        array_push($values, $limit, $offset);
        
        $jobs = $this->db->get_results($this->db->prepare($sql, ...$values));

        foreach ($jobs as $job) {
            $job->title = $job->job_title;
            $job->location = $job->city;
        }

        return $jobs;
    }

    // =====================================================
    // SECTION 6 : Count Jobs (For Pagination & Status Tabs)
    // =====================================================

    public function countJobs(array $args = []): int
    {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(job_title LIKE %s OR department LIKE %s OR city LIKE %s)';
            $search_term = '%' . $this->db->esc_like($args['search']) . '%';
            array_push($values, $search_term, $search_term, $search_term);
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE {$whereSql}";

        if (!empty($values)) {
            return (int) $this->db->get_var($this->db->prepare($sql, ...$values));
        }

        return (int) $this->db->get_var($sql);
    }

    // =====================================================
    // SECTION 7 : Bulk Actions
    // =====================================================

    public function bulkUpdateStatus(array $ids, string $status): bool
    {
        if (empty($ids)) return false;
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = $this->db->prepare("UPDATE {$this->table} SET status = %s WHERE id IN ($placeholders)", $status, ...$ids);
        
        return (bool) $this->db->query($sql);
    }

    public function bulkDelete(array $ids): bool
    {
        if (empty($ids)) return false;
        
        foreach ($ids as $id) {
            delete_post_meta((int)$id, '_nkrp_notification_email');
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = $this->db->prepare("DELETE FROM {$this->table} WHERE id IN ($placeholders)", ...$ids);
        
        return (bool) $this->db->query($sql);
    }

    // =====================================================
    // SECTION 8 : SEO Slug Helpers
    // =====================================================

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE job_slug = %s";
        $args = [$slug];

        if ($excludeId > 0) {
            $sql .= " AND id != %d";
            $args[] = $excludeId;
        }

        $count = (int) $this->db->get_var($this->db->prepare($sql, ...$args));

        return $count > 0;
    }

    public function findBySlug(string $slug): ?object
    {
        $job = $this->db->get_row($this->db->prepare("SELECT * FROM {$this->table} WHERE job_slug=%s", $slug));
        
        if ($job) {
            $job->title = $job->job_title;
            $job->location = $job->city;
            $job->currency = $job->salary_currency;
            $job->job_type = $job->employment_type;
            
            // NEW: Retrieve Notification Email from Meta
            $job->notification_email = get_post_meta($job->id, '_nkrp_notification_email', true);
        }
        return $job;
    }
}