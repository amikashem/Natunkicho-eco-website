<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Repositories;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Resume\Models\Resume;

if (!defined('ABSPATH')) {
    exit;
}

class ResumeRepository
{
    private \wpdb $db;
    private string $table;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->table = DatabaseManager::table('resumes');
    }

    // =====================================================
    // 1. Create Resume
    // =====================================================

    public function create(Resume $resume): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_resumes';

        $wpdb->insert(
            $table,
            [
                'user_id'         => $resume->user_id,
                'candidate_id'    => $resume->candidate_id,
                'resume_title'    => $resume->resume_title,
                'objective'       => $resume->objective,
                'experience_data' => $resume->experience_data,
                'education_data'  => $resume->education_data,
                'skills_data'     => $resume->skills_data,
                // CRITICAL: We must explicitly tell the DB to save these!
                'file_path'       => $resume->file_path, 
                'file_type'       => $resume->file_type,
                'is_primary'      => $resume->is_primary,
                'status'          => $resume->status
            ],
            // Format array: %s for strings, %d for integers
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s'] 
        );

        return (int) $wpdb->insert_id;
    }
    
    // =====================================================
    // 2. Find Resume
    // =====================================================

    public function find(int $id): ?object
    {
        return $this->db->get_row($this->db->prepare("SELECT * FROM {$this->table} WHERE id=%d", $id));
    }

    // =====================================================
    // 3. Update Resume
    // =====================================================

    public function update(Resume $resume): bool
    {
        return (bool) $this->db->update(
            $this->table,
            [
                'resume_title'        => $resume->resume_title,
                'objective'           => $resume->objective,
                'education_data'      => $resume->education_data,
                'experience_data'     => $resume->experience_data,
                'skills_data'         => $resume->skills_data,
                'certifications_data' => $resume->certifications_data,
                'languages_data'      => $resume->languages_data,
                'portfolio_data'      => $resume->portfolio_data,
                'ai_parsed_data'      => $resume->ai_parsed_data,
                'file_path'           => $resume->file_path,
                'file_type'           => $resume->file_type,
                'is_primary'          => $resume->is_primary,
                'status'              => $resume->status,
            ],
            ['id' => $resume->id]
        );
    }

    // =====================================================
    // 4. Delete Resume
    // =====================================================

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete($this->table, ['id' => $id]);
    }

    // =====================================================
    // 5. Get Resumes (Search & Pagination)
    // =====================================================

    public function getResumes(array $args = []): array
    {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['candidate_id'])) {
            $where[] = 'candidate_id = %d';
            $values[] = (int) $args['candidate_id'];
        }

        if (!empty($args['search'])) {
            $where[] = '(resume_title LIKE %s OR objective LIKE %s OR skills_data LIKE %s)';
            $search_term = '%' . $this->db->esc_like($args['search']) . '%';
            array_push($values, $search_term, $search_term, $search_term);
        }

        $whereSql = implode(' AND ', $where);
        
        $limit = (int) ($args['limit'] ?? 15);
        $offset = (int) ($args['offset'] ?? 0);
        $orderBy = sanitize_sql_orderby($args['orderby'] ?? 'id DESC');

        $sql = "SELECT * FROM {$this->table} WHERE {$whereSql} ORDER BY {$orderBy} LIMIT %d OFFSET %d";
        array_push($values, $limit, $offset);
        
        return $this->db->get_results($this->db->prepare($sql, ...$values));
    }

    // =====================================================
    // 6. Count Resumes
    // =====================================================

    public function countResumes(array $args = []): int
    {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['candidate_id'])) {
            $where[] = 'candidate_id = %d';
            $values[] = (int) $args['candidate_id'];
        }

        if (!empty($args['search'])) {
            $where[] = '(resume_title LIKE %s OR objective LIKE %s OR skills_data LIKE %s)';
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
    // 7. Bulk Actions
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
        
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = $this->db->prepare("DELETE FROM {$this->table} WHERE id IN ($placeholders)", ...$ids);
        return (bool) $this->db->query($sql);
    }
}