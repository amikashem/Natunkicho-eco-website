<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate\Services;

use NKRecruitment\Candidate\Models\Candidate;
use NKRecruitment\Candidate\Repositories\CandidateRepository;

if (!defined('ABSPATH')) {
    exit;
}

class CandidateService
{
    private CandidateRepository $repository;

    public function __construct()
    {
        $this->repository = new CandidateRepository();
    }

    // =====================================================
    // Create Candidate
    // =====================================================

    public function create(Candidate $candidate): int
    {
        // Business Logic: You could add validation here, e.g., check if email exists
        return $this->repository->create($candidate);
    }

    // =====================================================
    // Find Candidate
    // =====================================================

    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    // =====================================================
    // Update Candidate
    // =====================================================

    public function update(Candidate $candidate): bool
    {
        return $this->repository->update($candidate);
    }

    // =====================================================
    // Delete Candidate
    // =====================================================

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    // =====================================================
    // Get Candidates List
    // =====================================================

    public function getCandidates(array $args = []): array
    {
        return $this->repository->getCandidates($args);
    }

    // =====================================================
    // Count Candidates
    // =====================================================

    public function countCandidates(array $args = []): int
    {
        return $this->repository->countCandidates($args);
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
    
    /**
     * Calculates the true Profile Completion Percentage (0-100)
     */
    public function calculateCompletionScore(\NKRecruitment\Candidate\Models\Candidate $candidate, bool $is_email_verified, bool $has_uploaded_cv): int
    {
        $score = 0;

        // 1. Account Basics (30%)
        if ($is_email_verified) $score += 20;
        if (!empty($candidate->first_name) && !empty($candidate->last_name)) $score += 10;

        // 2. Professional Details (30%)
        if (!empty($candidate->professional_title)) $score += 10;
        if (!empty($candidate->bio)) $score += 10;
        if (!empty($candidate->skills)) $score += 10;

        // 3. Demographics & Contact (10%)
        if (!empty($candidate->location_city) && !empty($candidate->location_country)) $score += 5;
        if (!empty($candidate->phone) || !empty($candidate->whatsapp_number)) $score += 5;

        // 4. Social & Media (10%)
        if ($candidate->profile_photo_id > 0) $score += 5;
        if (!empty($candidate->linkedin_url) || !empty($candidate->portfolio_url)) $score += 5;

        // 5. The Resume/CV (20%)
        if ($has_uploaded_cv) $score += 20;

        return min(100, $score); // Cap at 100% just in case
    }
}