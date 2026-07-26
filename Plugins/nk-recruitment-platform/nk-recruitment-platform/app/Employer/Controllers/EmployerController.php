<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Controllers;

use NKRecruitment\Employer\Models\Company;
use NKRecruitment\Employer\Services\CompanyService;

if (!defined('ABSPATH')) {
    exit;
}

class EmployerController
{
    private CompanyService $service;

    public function __construct()
    {
        $this->service = new CompanyService();
    }

   public function companyList(): void
    {
        // --- 🚨 TEMPORARY DATABASE FIX & SYNC (Run Once) 🚨 ---
        global $wpdb;
        $table = \NKRecruitment\Database\DatabaseManager::table('companies');
        $wpdb->suppress_errors = true;
        
        // 1. Force the database to create the missing column!
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN company_slug VARCHAR(255) DEFAULT NULL");
        
        // 2. Find all companies that have an empty slug and fix them
        $empty_companies = $wpdb->get_results("SELECT id, company_name FROM {$table} WHERE company_slug = '' OR company_slug IS NULL");
        if (!empty($empty_companies)) {
            foreach ($empty_companies as $c) {
                $slug = sanitize_title($c->company_name);
                if (empty($slug)) $slug = 'company-' . $c->id;
                $wpdb->update($table, ['company_slug' => $slug], ['id' => $c->id]);
            }
        }
        $wpdb->suppress_errors = false;
        // --------------------------------------------------------

        // ==========================================
        // Delete Company
        // ==========================================
        if (
            isset($_GET['action'], $_GET['id']) &&
            $_GET['action'] === 'delete'
        ) {
            $id = (int) $_GET['id'];
            check_admin_referer('delete_company_' . $id);
            $this->service->delete($id);   

            wp_redirect(admin_url('admin.php?page=nkrp-companies&deleted=1'));
            exit;
        }
            
        // ==========================================
        // Bulk Actions
        // ==========================================
        if (isset($_POST['apply_bulk']) && !empty($_POST['bulk_action']) && !empty($_POST['companies'])) {
            check_admin_referer('bulk_company_action');
            
            $action = sanitize_text_field($_POST['bulk_action']);
            $ids = array_map('intval', $_POST['companies']);
        
            switch ($action) {
                case 'delete': foreach ($ids as $id) $this->service->delete($id); break;
                case 'activate': foreach ($ids as $id) $this->service->updateStatus($id, 'active'); break;
                case 'deactivate': foreach ($ids as $id) $this->service->updateStatus($id, 'inactive'); break;
                case 'verify': foreach ($ids as $id) $this->service->verify($id); break;
                case 'feature': foreach ($ids as $id) $this->service->feature($id); break;
            }
                        
            wp_redirect(admin_url('admin.php?page=nkrp-companies'));
            exit;
        }
                    
        // ==========================================
        // Search, Filters & Pagination
        // ==========================================
        $search = sanitize_text_field($_GET['s'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $industry = sanitize_text_field($_GET['industry'] ?? '');
        
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $perPage = 10;
        
        $totalCompanies = $this->service->count($search, $status, $industry);
        $totalPages = (int) ceil($totalCompanies / $perPage);
        
        $companies = $this->service->all($search, $status, $industry, $page, $perPage);
        $stats = $this->service->stats();
        
        require NKRP_PLUGIN_PATH . 'app/Employer/Views/company-list.php';
    }

    public function companyCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_company');

            $company = new Company();
            $company->user_id = get_current_user_id();
            $company->company_name = sanitize_text_field($_POST['company_name']);
            
            // FIXED: Removed manual slug assignment so the Service generates it automatically!

            $company->company_email = sanitize_email($_POST['company_email']);
            $company->industry = sanitize_text_field($_POST['industry'] ?? '');
            $company->company_size = sanitize_text_field($_POST['company_size'] ?? '');
            $company->founded_year = (int)($_POST['founded_year'] ?? 0);

            $website = trim($_POST['website'] ?? '');
            if ($website !== '') {
                if (!preg_match('#^https?://#i', $website)) {
                    $website = 'https://' . $website;
                }
                $website = esc_url_raw($website);
            }
            
            $company->website = $website;
            $company->logo = esc_url_raw($_POST['logo'] ?? '');
            $company->cover = esc_url_raw($_POST['cover'] ?? '');
            $company->phone = sanitize_text_field($_POST['phone']);
            $company->status = 'active';
            $company->country = sanitize_text_field($_POST['country'] ?? '');
            $company->state = sanitize_text_field($_POST['state'] ?? '');
            $company->city = sanitize_text_field($_POST['city'] ?? '');
            $company->address = sanitize_textarea_field($_POST['address'] ?? '');
            $company->description = wp_kses_post($_POST['description'] ?? '');
            $company->status = sanitize_text_field($_POST['status'] ?? 'active');
            $company->verified = isset($_POST['verified']) ? 1 : 0;
            $company->featured = isset($_POST['featured']) ? 1 : 0;

            $this->service->create($company);
            wp_redirect(admin_url('admin.php?page=nkrp-companies'));
            exit;
        }

        require NKRP_PLUGIN_PATH . 'app/Employer/Views/company-create.php';
    }

    // =====================================================
    // SECTION 5: Edit Company
    // =====================================================

    public function companyEdit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            wp_die(__('Invalid company ID.', 'nk-recruitment'));
        }

        $company = $this->service->find($id);

        if (!$company) {
            wp_die(__('Company not found.', 'nk-recruitment'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_company');

            $model = new Company();
            $model->id = $id;
            $model->user_id = (int) $company->user_id;

            // Basic Information
            $model->company_name = sanitize_text_field($_POST['company_name']);
            
            // FIXED: Removed manual slug assignment so the Service generates it automatically!
            
            $model->company_email = sanitize_email($_POST['company_email']);
            $model->phone = sanitize_text_field($_POST['phone']);

            // Website
            $website = trim($_POST['website'] ?? '');
            if ($website !== '') {
                if (!preg_match('#^https?://#i', $website)) {
                    $website = 'https://' . $website;
                }
                $website = esc_url_raw($website);
            }
            $model->website = $website;

            // Images
            $model->logo = esc_url_raw($_POST['logo'] ?? '');
            $model->cover = esc_url_raw($_POST['cover'] ?? '');

            // Company Details
            $model->industry = sanitize_text_field($_POST['industry'] ?? '');
            $model->company_size = sanitize_text_field($_POST['company_size'] ?? '');
            $model->founded_year = (int) ($_POST['founded_year'] ?? 0);

            // Address
            $model->country = sanitize_text_field($_POST['country'] ?? '');
            $model->state = sanitize_text_field($_POST['state'] ?? '');
            $model->city = sanitize_text_field($_POST['city'] ?? '');
            $model->address = sanitize_textarea_field($_POST['address'] ?? '');

            // Description
            $model->description = wp_kses_post($_POST['description'] ?? '');

            // Status
            $model->status = sanitize_text_field($_POST['status'] ?? 'active');
            $model->verified = isset($_POST['verified']) ? 1 : 0;
            $model->featured = isset($_POST['featured']) ? 1 : 0;

            $this->service->update($model);

            wp_redirect(admin_url('admin.php?page=nkrp-companies'));
            exit;
        }

        require NKRP_PLUGIN_PATH . 'app/Employer/Views/company-edit.php';
    }
}