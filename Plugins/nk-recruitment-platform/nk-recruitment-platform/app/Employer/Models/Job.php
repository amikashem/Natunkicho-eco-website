<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Models;

class Job
{
    public int $id = 0;

    public int $company_id = 0;

    public int $user_id = 0;

    public string $title = '';

    public string $slug = '';

    public string $description = '';

    public string $requirements = '';

    public string $benefits = '';

    public string $category = '';

    public string $employment_type = '';

    public string $experience_level = '';

    public string $salary_type = '';

    public string $salary_min = '';

    public string $salary_max = '';

    public string $currency = 'USD';

    public string $country = '';

    public string $state = '';

    public string $city = '';

    public string $status = 'active';

    public int $featured = 0;

    public string $created_at = '';

    public string $updated_at = '';
}