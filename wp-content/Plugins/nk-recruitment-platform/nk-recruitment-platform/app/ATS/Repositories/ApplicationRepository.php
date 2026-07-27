<?php
declare(strict_types=1);
namespace NKRecruitment\ATS\Repositories;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\ATS\Models\Application;

if (!defined('ABSPATH')) exit;

class ApplicationRepository
{
    private \wpdb $db;
    private string $table;

    public function __construct() {
        $this->db = DatabaseManager::db();
        $this->table = DatabaseManager::table('applications');
    }

    public function create(Application $app): int {
        $this->db->insert($this->table, [
            'job_id' => $app->job_id, 'candidate_id' => $app->candidate_id, 'company_id' => $app->company_id,
            'resume_id' => $app->resume_id, 'cover_letter' => $app->cover_letter, 'status' => $app->status,
            'employer_rating' => $app->employer_rating, 'employer_notes' => $app->employer_notes
        ]);
        return (int) $this->db->insert_id;
    }

    public function find(int $id): ?object {
        // Upgraded to pull related names!
        $sql = "SELECT a.*, j.job_title, c.company_name, CONCAT(cand.first_name, ' ', cand.last_name) as candidate_name 
                FROM {$this->table} a
                LEFT JOIN {$this->db->prefix}nkrp_jobs j ON a.job_id = j.id
                LEFT JOIN {$this->db->prefix}nkrp_companies c ON a.company_id = c.id
                LEFT JOIN {$this->db->prefix}nkrp_candidates cand ON a.candidate_id = cand.id
                WHERE a.id = %d";
        return $this->db->get_row($this->db->prepare($sql, $id));
    }

    public function update(Application $app): bool {
        return (bool) $this->db->update($this->table, [
            'status' => $app->status, 'employer_rating' => $app->employer_rating, 'employer_notes' => $app->employer_notes
        ], ['id' => $app->id]);
    }

    public function delete(int $id): bool {
        return (bool) $this->db->delete($this->table, ['id' => $id]);
    }

    public function getApplications(array $args = []): array {
        $where = ['1=1']; $values = [];

        if (!empty($args['status'])) { $where[] = 'a.status = %s'; $values[] = $args['status']; }
        if (!empty($args['job_id'])) { $where[] = 'a.job_id = %d'; $values[] = (int) $args['job_id']; }
        
        $whereSql = implode(' AND ', $where);
        $limit = (int) ($args['limit'] ?? 15);
        $offset = (int) ($args['offset'] ?? 0);

        // N+1 Query Killer: One query to rule them all
        $sql = "SELECT a.*, j.job_title, c.company_name, CONCAT(cand.first_name, ' ', cand.last_name) as candidate_name 
                FROM {$this->table} a
                LEFT JOIN {$this->db->prefix}nkrp_jobs j ON a.job_id = j.id
                LEFT JOIN {$this->db->prefix}nkrp_companies c ON a.company_id = c.id
                LEFT JOIN {$this->db->prefix}nkrp_candidates cand ON a.candidate_id = cand.id
                WHERE {$whereSql} ORDER BY a.id DESC LIMIT %d OFFSET %d";
                
        array_push($values, $limit, $offset);
        return $this->db->get_results($this->db->prepare($sql, ...$values));
    }

    public function countApplications(array $args = []): int {
        $where = ['1=1']; $values = [];
        if (!empty($args['status'])) { $where[] = 'status = %s'; $values[] = $args['status']; }
        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE {$whereSql}";
        return !empty($values) ? (int) $this->db->get_var($this->db->prepare($sql, ...$values)) : (int) $this->db->get_var($sql);
    }

    public function bulkUpdateStatus(array $ids, string $status): bool {
        if (empty($ids)) return false;
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        return (bool) $this->db->query($this->db->prepare("UPDATE {$this->table} SET status = %s WHERE id IN ($placeholders)", $status, ...$ids));
    }

    public function bulkDelete(array $ids): bool {
        if (empty($ids)) return false;
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        return (bool) $this->db->query($this->db->prepare("DELETE FROM {$this->table} WHERE id IN ($placeholders)", ...$ids));
    }
}