<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Services;

use NKRecruitment\Jobs\Models\Job;
use NKRecruitment\Jobs\Repositories\JobRepository;

if (!defined('ABSPATH')) {
    exit;
}

class JobService
{
    private JobRepository $repository;

    public function __construct()
    {
        $this->repository = new JobRepository();
    }

    // =====================================================
    // Create Job
    // =====================================================
    public function create(Job $job): int
    {
        // FIX: Access $job->title, not $job->job_title
        $title = $job->title ?? 'Untitled Job';
        $job->slug = $this->generateUniqueSlug($title);
        
        return $this->repository->create($job);
    }

    // =====================================================
    // Find Job
    // =====================================================
    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    // =====================================================
    // Update Job
    // =====================================================
    public function update(Job $job): bool
    {
        // FIX: Access $job->title, not $job->job_title
        if (empty($job->slug)) {
            $title = $job->title ?? 'Untitled Job';
            $job->slug = $this->generateUniqueSlug($title, $job->id);
        }
        
        return $this->repository->update($job);
    }

    // =====================================================
    // Delete Job
    // =====================================================
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    // =====================================================
    // Get List of Jobs
    // =====================================================
    public function getJobs(array $args = []): array
    {
        return $this->repository->getJobs($args);
    }

    // =====================================================
    // Count Jobs
    // =====================================================
    public function countJobs(array $args = []): int
    {
        return $this->repository->countJobs($args);
    }

    // =====================================================
    // Bulk Actions
    // =====================================================
    public function bulkUpdateStatus(array $ids, string $status): bool
    {
        return $this->repository->bulkUpdateStatus($ids, $status);
    }

    public function bulkDelete(array $ids): bool
    {
        return $this->repository->bulkDelete($ids);
    }

    // =====================================================
    // Helper: Generate Unique Slug
    // =====================================================
    private function generateUniqueSlug(string $title, int $excludeId = 0): string
    {
        $slug = sanitize_title($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->repository->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}