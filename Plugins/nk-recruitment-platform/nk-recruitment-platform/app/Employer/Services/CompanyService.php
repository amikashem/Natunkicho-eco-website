<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Services;

use NKRecruitment\Employer\Models\Company;
use NKRecruitment\Employer\Repositories\CompanyRepository;

class CompanyService
{
    private CompanyRepository $repository;

    public function __construct()
    {
        $this->repository = new CompanyRepository();
    }

    // =====================================================
    // Create Company
    // =====================================================

    public function create(Company $company): int
    {
        // Generate a perfectly unique slug based on the company name
        $name = $company->company_name ?? '';
        $company->company_slug = $this->generateUniqueSlug($name);

        return $this->repository->create($company);
    }

    // =====================================================
    // Get Companies & Stats
    // =====================================================

    public function all(string $search = '', string $status = '', string $industry = '', int $page = 1, int $perPage = 10): array
    {
        return $this->repository->all($search, $status, $industry, $page, $perPage);
    }

    public function count(string $search = '', string $status = '', string $industry = ''): int
    {
        return $this->repository->count($search, $status, $industry);
    }

    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    public function stats(): array
    {
        return $this->repository->stats();
    }

    // =====================================================
    // Update Company
    // =====================================================

    public function update(Company $company): bool
    {
        // Ensure slug exists during an update
        if (empty($company->company_slug)) {
            $name = $company->company_name ?? '';
            $company->company_slug = $this->generateUniqueSlug($name, (int)($company->id ?? 0));
        }

        return $this->repository->update($company);
    }
    
    public function updateStatus(int $id, string $status): bool
    {
        return $this->repository->updateStatus($id, $status);
    }

    public function verify(int $id): bool
    {
        return $this->repository->verify($id);
    }
    
    public function feature(int $id): bool
    {
        return $this->repository->feature($id);
    }

    // =====================================================
    // Delete Company
    // =====================================================

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    // =====================================================
    // Helper: Generate Unique SEO Slug (NEW)
    // =====================================================

    /**
     * Converts a company name into a URL-friendly slug and ensures it does not exist in the database.
     */
    private function generateUniqueSlug(string $name, int $excludeId = 0): string
    {
        $slug = sanitize_title($name);
        
        if (empty($slug)) {
            $slug = 'company-' . time();
        }

        $originalSlug = $slug;
        $counter = 1;

        while ($this->repository->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}