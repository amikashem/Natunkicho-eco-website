<?php
declare(strict_types=1);
namespace NKRecruitment\ATS\Services;

use NKRecruitment\ATS\Models\Application;
use NKRecruitment\ATS\Repositories\ApplicationRepository;

if (!defined('ABSPATH')) exit;

class ApplicationService
{
    private ApplicationRepository $repository;

    public function __construct() { $this->repository = new ApplicationRepository(); }
    public function create(Application $app): int { return $this->repository->create($app); }
    public function find(int $id): ?object { return $this->repository->find($id); }
    public function update(Application $app): bool { return $this->repository->update($app); }
    public function delete(int $id): bool { return $this->repository->delete($id); }
    public function getApplications(array $args = []): array { return $this->repository->getApplications($args); }
    public function countApplications(array $args = []): int { return $this->repository->countApplications($args); }
    public function bulkUpdateStatus(array $ids, string $status): bool { return $this->repository->bulkUpdateStatus($ids, $status); }
    public function bulkDelete(array $ids): bool { return $this->repository->bulkDelete($ids); }
}