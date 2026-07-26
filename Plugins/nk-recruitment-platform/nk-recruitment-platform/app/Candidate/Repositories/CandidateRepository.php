<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate\Repositories;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Candidate\Models\Candidate;

if (!defined('ABSPATH')) {
    exit;
}

class CandidateRepository
{
    private \wpdb $db;
    private string $table;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->table = DatabaseManager::table('candidates');
    }

    // =====================================================
    // 1. Create Candidate
    // =====================================================

    public function create(Candidate $candidate): int
    {
        $this->db->insert(
            $this->table,
            [
                'user_id'            => $candidate->user_id,
                'first_name'         => $candidate->first_name,
                'last_name'          => $candidate->last_name,
                'email'              => $candidate->email,
                'phone'              => $candidate->phone,
                'professional_title' => $candidate->professional_title,
                'location_city'      => $candidate->location_city,
                'location_country'   => $candidate->location_country,
                'date_of_birth'      => $candidate->date_of_birth,
                'gender'             => $candidate->gender,
                'nationality'        => $candidate->nationality,
                'current_salary'     => $candidate->current_salary,
                'expected_salary'    => $candidate->expected_salary,
                'salary_currency'    => $candidate->salary_currency,
                'experience_years'   => $candidate->experience_years,
                'education_level'    => $candidate->education_level,
                'availability'       => $candidate->availability,
                'bio'                => $candidate->bio,
                'skills'             => $candidate->skills,
                'languages'          => $candidate->languages,
                'linkedin_url'       => $candidate->linkedin_url,
                'portfolio_url'      => $candidate->portfolio_url,
                'profile_photo_id'   => $candidate->profile_photo_id,
                'resume_file_id'     => $candidate->resume_file_id,
                'is_featured'        => $candidate->is_featured,
                'status'             => $candidate->status,
            ]
        );

        if ($this->db->last_error) {
            wp_die('<pre>Database Error (Candidates): ' . esc_html($this->db->last_error) . '</pre>');
        }

        return (int) $this->db->insert_id;
    }

    // =====================================================
    // 2. Find Candidate
    // =====================================================

    public function find(int $id): ?object
    {
        return $this->db->get_row($this->db->prepare("SELECT * FROM {$this->table} WHERE id=%d", $id));
    }

    // =====================================================
    // 3. Update Candidate
    // =====================================================

    public function update(Candidate $candidate): bool
    {
        return (bool) $this->db->update(
            $this->table,
            [
                'first_name'         => $candidate->first_name,
                'last_name'          => $candidate->last_name,
                'email'              => $candidate->email,
                'phone'              => $candidate->phone,
                'professional_title' => $candidate->professional_title,
                'location_city'      => $candidate->location_city,
                'location_country'   => $candidate->location_country,
                'date_of_birth'      => $candidate->date_of_birth,
                'gender'             => $candidate->gender,
                'nationality'        => $candidate->nationality,
                'current_salary'     => $candidate->current_salary,
                'expected_salary'    => $candidate->expected_salary,
                'salary_currency'    => $candidate->salary_currency,
                'experience_years'   => $candidate->experience_years,
                'education_level'    => $candidate->education_level,
                'availability'       => $candidate->availability,
                'bio'                => $candidate->bio,
                'skills'             => $candidate->skills,
                'languages'          => $candidate->languages,
                'linkedin_url'       => $candidate->linkedin_url,
                'portfolio_url'      => $candidate->portfolio_url,
                'is_featured'        => $candidate->is_featured,
                'status'             => $candidate->status,
            ],
            ['id' => $candidate->id]
        );
    }

    // =====================================================
    // 4. Delete Candidate
    // =====================================================

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete($this->table, ['id' => $id]);
    }

    // =====================================================
    // 5. Get Candidates (Search & Pagination)
    // =====================================================

    public function getCandidates(array $args = []): array
    {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(first_name LIKE %s OR last_name LIKE %s OR professional_title LIKE %s OR email LIKE %s)';
            $search_term = '%' . $this->db->esc_like($args['search']) . '%';
            array_push($values, $search_term, $search_term, $search_term, $search_term);
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
    // 6. Count Candidates
    // =====================================================

    public function countCandidates(array $args = []): int
    {
        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[] = '(first_name LIKE %s OR last_name LIKE %s OR professional_title LIKE %s OR email LIKE %s)';
            $search_term = '%' . $this->db->esc_like($args['search']) . '%';
            array_push($values, $search_term, $search_term, $search_term, $search_term);
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