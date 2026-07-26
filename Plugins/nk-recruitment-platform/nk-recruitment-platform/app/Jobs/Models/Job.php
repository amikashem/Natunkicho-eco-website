<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Job
{
    public int $id = 0;
    public int $company_id = 0;
    public string $title = '';
    public string $slug = '';
    public string $job_type = '';
    public string $department = '';
    public string $location = '';
    public string $country = '';
    public float $salary_min = 0.00;
    public float $salary_max = 0.00;
    public string $currency = 'USD';
    public int $vacancies = 1;
    public string $experience = '';
    public string $education = '';
    public ?string $deadline = null;
    public string $description = '';
    public string $requirements = '';
    public string $responsibilities = '';
    public string $benefits = '';
    public string $status = 'draft';
    public int $featured = 0;
    
    // NEW FIELDS:
    public string $external_apply_url = '';
    public string $notification_email = '';
}