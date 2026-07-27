<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Repositories;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Employer\Models\Company;

class CompanyRepository
{
    private \wpdb $db;
    private string $table;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->table = DatabaseManager::table('companies');
    }

    // =====================================================
    // SECTION 1: Create
    // =====================================================

    public function create(Company $company): int
    {
        $result = $this->db->insert(
            $this->table,
            [
                'user_id'       => $company->user_id,
                'company_name'  => $company->company_name,
                'company_slug'  => $company->company_slug,
                'company_email' => $company->company_email,
                'phone'         => $company->phone,
                'website'       => $company->website,
                'logo'          => $company->logo,
                'industry'      => $company->industry,
                'company_size'  => $company->company_size,
                'founded_year'  => $company->founded_year,
                'cover'         => $company->cover,
                'country'       => $company->country,
                'state'         => $company->state,
                'city'          => $company->city,
                'address'       => $company->address,
                'description'   => $company->description,
                'verified'      => $company->verified,
                'featured'      => $company->featured,
                'status'        => $company->status,
            ]
        );

        if ($result === false) {
            wp_die(
                '<h2>Database Insert Error</h2><pre>' .
                esc_html($this->db->last_error) .
                '</pre>'
            );
        }

        return (int) $this->db->insert_id;
    }

    // =====================================================
    // SECTION 2: Find
    // =====================================================

    public function find(int $id): ?object
    {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE id=%d",
                $id
            )
        );
    }

    // =====================================================
    // SECTION 3: Get All Companies
    // =====================================================

    public function all(
        string $search = '',
        string $status = '',
        string $industry = '',
        int $page = 1,
        int $perPage = 10
    ): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $values = [];

        if ($search !== '') {
            $like = '%' . $this->db->esc_like($search) . '%';
            $sql .= "
            AND (
                company_name LIKE %s
                OR company_email LIKE %s
                OR website LIKE %s
            )";
            array_push($values, $like, $like, $like);
        }

        if ($status !== '') {
            $sql .= " AND status=%s";
            $values[] = $status;
        }

        if ($industry !== '') {
            $sql .= " AND industry=%s";
            $values[] = $industry;
        }

        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY id DESC LIMIT %d OFFSET %d";
        
        array_push($values, $perPage, $offset);

        return $this->db->get_results(
            $this->db->prepare($sql, ...$values)
        );
    }

    // =====================================================
    // SECTION 4: Update
    // =====================================================

    public function update(Company $company): bool
    {
        return (bool) $this->db->update(
            $this->table,
            [
                'company_name'  => $company->company_name,
                'company_slug'  => $company->company_slug,
                'company_email' => $company->company_email,
                'phone'         => $company->phone,
                'website'       => $company->website,
                'logo'          => $company->logo,
                'cover'         => $company->cover,
                'industry'      => $company->industry,
                'company_size'  => $company->company_size,
                'founded_year'  => $company->founded_year,
                'country'       => $company->country,
                'state'         => $company->state,
                'city'          => $company->city,
                'address'       => $company->address,
                'description'   => $company->description,
                'verified'      => $company->verified,
                'featured'      => $company->featured,
                'status'        => $company->status,
            ],
            [
                'id' => $company->id
            ]
        );
    }

    // =====================================================
    // SECTION 5: Count Companies
    // =====================================================

    public function count(
        string $search = '',
        string $status = '',
        string $industry = ''
    ): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $values = [];

        if ($search !== '') {
            $like = '%' . $this->db->esc_like($search) . '%';
            $sql .= "
            AND (
                company_name LIKE %s
                OR company_email LIKE %s
                OR website LIKE %s
            )";
            array_push($values, $like, $like, $like);
        }

        if ($status !== '') {
            $sql .= " AND status=%s";
            $values[] = $status;
        }

        if ($industry !== '') {
            $sql .= " AND industry=%s";
            $values[] = $industry;
        }

        if (!empty($values)) {
            return (int) $this->db->get_var(
                $this->db->prepare($sql, ...$values)
            );
        }

        return (int) $this->db->get_var($sql);
    }

    // =====================================================
    // SECTION 6: Delete & Status
    // =====================================================
    
    public function delete(int $id): bool
    {
        $result = $this->db->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );

        if ($result === false) {
            wp_die(
                '<h2>Delete Error</h2><pre>' .
                esc_html($this->db->last_error) .
                '</pre>'
            );
        }

        return $result > 0;
    }
                
    public function updateStatus(int $id, string $status): bool
    {
        return (bool) $this->db->update(
            $this->table,
            ['status' => $status],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
    } 

    // =====================================================
    // SECTION 7: Company Statistics & Toggles
    // =====================================================
    
    public function stats(): array
    {
        return [
            'total' => (int) $this->db->get_var("SELECT COUNT(*) FROM {$this->table}"),
            'active' => (int) $this->db->get_var("SELECT COUNT(*) FROM {$this->table} WHERE status='active'"),
            'pending' => (int) $this->db->get_var("SELECT COUNT(*) FROM {$this->table} WHERE status='pending'"),
            'featured' => (int) $this->db->get_var("SELECT COUNT(*) FROM {$this->table} WHERE featured=1"),
        ];
    }  
    
    public function verify(int $id): bool
    {
        return (bool) $this->db->update(
            $this->table,
            ['verified' => 1],
            ['id' => $id]
        );
    }

    public function feature(int $id): bool
    {
        return (bool) $this->db->update(
            $this->table,
            ['featured' => 1],
            ['id' => $id]
        );
    }

    // =====================================================
    // SECTION 8: SEO Slug Helpers (THE MISSING PIECE)
    // =====================================================

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE company_slug = %s";
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
        return $this->db->get_row($this->db->prepare("SELECT * FROM {$this->table} WHERE company_slug=%s", $slug));
    }
}