<?php

declare(strict_types=1);

namespace NKRecruitment\Search\Repositories;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class SearchRepository
{
    private \wpdb $db;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
    }

    // =====================================================
    // 1. DYNAMIC JOB SEARCH
    // =====================================================
    
    public function searchJobs(array $filters): array
    {
        $table = DatabaseManager::table('jobs');
        $compTable = DatabaseManager::table('companies');
        
        $where = ['j.status IN ("published", "active", "publish")']; 
        $values = [];

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $this->db->esc_like($filters['keyword']) . '%';
            $where[] = '(j.job_title LIKE %s OR j.description LIKE %s OR j.requirements LIKE %s)';
            array_push($values, $keyword, $keyword, $keyword);
        }

        if (!empty($filters['location'])) {
            $loc = '%' . $this->db->esc_like($filters['location']) . '%';
            $where[] = '(j.city LIKE %s OR j.country LIKE %s OR j.location LIKE %s)';
            array_push($values, $loc, $loc, $loc);
        }

        $exact_fields = ['employment_type', 'department', 'experience_level'];
        foreach ($exact_fields as $field) {
            if (!empty($filters[$field])) {
                $where[] = "j.{$field} = %s";
                $values[] = $filters[$field];
            }
        }

        if (!empty($filters['min_salary'])) {
            $where[] = 'j.salary_min >= %d';
            $values[] = (int) $filters['min_salary'];
        }

        if (!empty($filters['featured'])) {
            $where[] = 'j.featured = 1';
        }

        $whereSql = implode(' AND ', $where);
        $limit  = (int) ($filters['limit'] ?? 12);
        $offset = (int) ($filters['offset'] ?? 0);
        $orderBy = sanitize_sql_orderby($filters['orderby'] ?? 'j.featured DESC, j.created_at DESC');

        $sql = "SELECT j.*, c.company_name, c.company_slug, c.logo as company_logo 
                FROM {$table} j 
                LEFT JOIN {$compTable} c ON j.company_id = c.id 
                WHERE {$whereSql} 
                ORDER BY {$orderBy} 
                LIMIT %d OFFSET %d";
                
        array_push($values, $limit, $offset);

        $results = $this->db->get_results($this->db->prepare($sql, ...$values));
        
        $countSql = "SELECT COUNT(j.id) FROM {$table} j WHERE {$whereSql}";
        $countValues = array_slice($values, 0, -2);
        $total = empty($countValues) ? $this->db->get_var($countSql) : $this->db->get_var($this->db->prepare($countSql, ...$countValues));

        return [
            'data' => $results,
            'meta' => [
                'total' => (int) $total,
                'limit' => $limit,
                'offset' => $offset
            ]
        ];
    }

    // =====================================================
    // 2. DYNAMIC COMPANY SEARCH
    // =====================================================

    public function searchCompanies(array $filters): array
    {
        $table = DatabaseManager::table('companies');
        $where = ['status = "active"']; 
        $values = [];

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $this->db->esc_like($filters['keyword']) . '%';
            $where[] = '(company_name LIKE %s OR description LIKE %s)';
            array_push($values, $keyword, $keyword);
        }

        if (!empty($filters['location'])) {
            $loc = '%' . $this->db->esc_like($filters['location']) . '%';
            $where[] = '(city LIKE %s OR state LIKE %s OR country LIKE %s)';
            array_push($values, $loc, $loc, $loc);
        }

        if (!empty($filters['industry'])) {
            $where[] = 'industry = %s';
            $values[] = $filters['industry'];
        }

        if (!empty($filters['verified'])) {
            $where[] = 'verified = 1';
        }

        $whereSql = implode(' AND ', $where);
        $limit  = (int) ($filters['limit'] ?? 12);
        $offset = (int) ($filters['offset'] ?? 0);

        $sql = "SELECT id, company_name, company_slug, industry, city, country, logo, verified, featured 
                FROM {$table} WHERE {$whereSql} ORDER BY featured DESC, company_name ASC LIMIT %d OFFSET %d";
                
        array_push($values, $limit, $offset);
        
        $results = $this->db->get_results($this->db->prepare($sql, ...$values));
        
        $countSql = "SELECT COUNT(id) FROM {$table} WHERE {$whereSql}";
        $countValues = array_slice($values, 0, -2);
        $total = empty($countValues) ? $this->db->get_var($countSql) : $this->db->get_var($this->db->prepare($countSql, ...$countValues));

        return [
            'data' => $results,
            'meta' => ['total' => (int) $total, 'limit' => $limit, 'offset' => $offset]
        ];
    }

    // =====================================================
    // 3. DYNAMIC CANDIDATE SEARCH (FIXED & HIGHLY ACCURATE)
    // =====================================================

    public function searchCandidates(array $filters): array
    {
        $limit  = (int) ($filters['limit'] ?? 12);
        $offset = (int) ($filters['offset'] ?? 0);

        global $wpdb;
        $where = ["1=1"];
        $values = [];

        // Ensure user is a candidate
        $cap_key = $wpdb->prefix . 'capabilities';
        $where[] = "u.ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s)";
        array_push($values, $cap_key, '%candidate%');

        // POWERFUL ROLE / NAME SEARCH
        if (!empty($filters['role']) || !empty($filters['keyword'])) {
            $term = !empty($filters['role']) ? $filters['role'] : $filters['keyword'];
            $term_sql = '%' . $wpdb->esc_like($term) . '%';
            $where[] = "(
                u.display_name LIKE %s OR 
                u.user_login LIKE %s OR 
                u.user_email LIKE %s OR 
                EXISTS (
                    SELECT 1 FROM {$wpdb->usermeta} um 
                    WHERE um.user_id = u.ID 
                    AND um.meta_key IN ('_nkrp_professional_title', 'first_name', 'last_name', '_nkrp_bio') 
                    AND um.meta_value LIKE %s
                )
            )";
            array_push($values, $term_sql, $term_sql, $term_sql, $term_sql);
        }
        
        // POWERFUL SKILL SEARCH
        if (!empty($filters['skill'])) {
            $skill_term = '%' . $wpdb->esc_like($filters['skill']) . '%';
            $where[] = "EXISTS (
                SELECT 1 FROM {$wpdb->usermeta} ums 
                WHERE ums.user_id = u.ID 
                AND ums.meta_key = '_nkrp_skills' 
                AND ums.meta_value LIKE %s
            )";
            array_push($values, $skill_term);
        }

        // Location Search
        if (!empty($filters['location'])) {
            $loc = '%' . $wpdb->esc_like($filters['location']) . '%';
            $where[] = "EXISTS (
                SELECT 1 FROM {$wpdb->usermeta} um_loc 
                WHERE um_loc.user_id = u.ID 
                AND um_loc.meta_key IN ('_nkrp_city', '_nkrp_country', '_nkrp_location') 
                AND um_loc.meta_value LIKE %s
            )";
            $values[] = $loc;
        }

        $whereSql = implode(' AND ', $where);
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS u.ID FROM {$wpdb->users} u WHERE {$whereSql} ORDER BY u.user_registered DESC LIMIT %d OFFSET %d";
        array_push($values, $limit, $offset);

        $user_ids = $wpdb->get_col($wpdb->prepare($sql, ...$values));
        $total = (int) $wpdb->get_var("SELECT FOUND_ROWS()");

        $results = [];
        foreach ($user_ids as $uid) {
            $user = get_userdata((int)$uid);
            if (!$user) continue;

            $raw_skills = get_user_meta($user->ID, '_nkrp_skills', true);
            $skills_array = !empty($raw_skills) ? array_map('trim', explode(',', $raw_skills)) : [];
            
            $location = get_user_meta($user->ID, '_nkrp_city', true);
            if (empty($location)) {
                $location = get_user_meta($user->ID, '_nkrp_country', true) ?: 'Open to Relocation';
            }

            // Find the active resume ID so the Profile button actually works!
            $resume_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}nkrp_resumes WHERE user_id = %d AND status='active' ORDER BY id DESC LIMIT 1", $user->ID));

            $results[] = (object) [
                'id'                 => $resume_id > 0 ? $resume_id : $user->ID, 
                'user_id'            => $user->ID,
                'display_name'       => trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name,
                'professional_title' => get_user_meta($user->ID, '_nkrp_professional_title', true),
                'location'           => $location,
                'experience_years'   => get_user_meta($user->ID, '_nkrp_experience_years', true),
                'skills_data'        => $skills_array, 
                'profile_photo_id'   => get_user_meta($user->ID, '_nkrp_photo_id', true)
            ];
        }

        return [
            'data' => $results,
            'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset]
        ];
    }
}