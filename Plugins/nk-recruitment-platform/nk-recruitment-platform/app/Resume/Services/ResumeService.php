<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Services;

use NKRecruitment\Resume\Models\Resume;
use NKRecruitment\Resume\Repositories\ResumeRepository;

if (!defined('ABSPATH')) {
    exit;
}

class ResumeService
{
    private ResumeRepository $repository;

    public function __construct()
    {
        $this->repository = new ResumeRepository();
    }

    public function create(Resume $resume): int
    {
        return $this->repository->create($resume);
    }

    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    public function update(Resume $resume): bool
    {
        return $this->repository->update($resume);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getResumes(array $args = []): array
    {
        return $this->repository->getResumes($args);
    }

    public function countResumes(array $args = []): int
    {
        return $this->repository->countResumes($args);
    }

    public function bulkUpdateStatus(array $ids, string $status): bool
    {
        return $this->repository->bulkUpdateStatus($ids, $status);
    }

    public function bulkDelete(array $ids): bool
    {
        return $this->repository->bulkDelete($ids);
    }
}