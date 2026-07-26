<?php

declare(strict_types=1);

namespace NKRecruitment\Search\Services;

use NKRecruitment\Search\Repositories\SearchRepository;
use NKRecruitment\Search\Models\SearchFilter;
use NKRecruitment\Membership\Services\PermissionService;

if (!defined('ABSPATH')) {
    exit;
}

class SearchService
{
    private SearchRepository $repository;
    private PermissionService $permissions;

    public function __construct()
    {
        $this->repository = new SearchRepository();
        $this->permissions = new PermissionService();
    }

    private function formatResponse(array $repoResult, SearchFilter $filter): array
    {
        $total = $repoResult['meta']['total'] ?? 0;
        $total_pages = $filter->limit > 0 ? (int) ceil($total / $filter->limit) : 1;

        return [
            'success' => true,
            'data'    => $repoResult['data'] ?? [],
            'meta'    => [
                'total_results' => $total,
                'current_page'  => $filter->page,
                'total_pages'   => $total_pages,
                'per_page'      => $filter->limit,
                'has_more'      => $filter->page < $total_pages
            ]
        ];
    }

    // =====================================================
    // CORE SEARCH EXECUTIONS (With Phase 5 Security)
    // =====================================================

    public function searchJobs(array $rawInput): array
    {
        // Security Check: Strip Premium Filters if Free User
        if (!$this->permissions->canUsePremiumFilters(get_current_user_id())) {
            unset($rawInput['salary_min']);
            unset($rawInput['salary_max']);
            unset($rawInput['featured']);
            unset($rawInput['remote']);
        }

        $filter = SearchFilter::fromArray($rawInput);
        $result = $this->repository->searchJobs($filter->toArray());
        return $this->formatResponse($result, $filter);
    }

    public function searchCompanies(array $rawInput): array
    {
        $filter = SearchFilter::fromArray($rawInput);
        $result = $this->repository->searchCompanies($filter->toArray());
        return $this->formatResponse($result, $filter);
    }

    public function searchCandidates(array $rawInput): array
    {
        $filter = SearchFilter::fromArray($rawInput);
        $result = $this->repository->searchCandidates($filter->toArray());
        return $this->formatResponse($result, $filter);
    }
}