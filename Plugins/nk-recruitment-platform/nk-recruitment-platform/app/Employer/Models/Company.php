<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Models;

class Company
{
    public ?int $id = null;
    public int $user_id;

    public string $company_name = '';
    public string $company_slug = '';

    public ?string $company_email = null;
    public ?string $phone = null;
    public ?string $website = null;

    public ?string $logo = null;
    public ?string $cover = null;

    public ?string $industry = null;
    public ?string $company_size = null;

    public ?int $founded_year = null;

    public ?string $country = null;
    public ?string $state = null;
    public ?string $city = null;

    public ?string $address = null;

    public ?string $description = null;

    public int $verified = 0;
    public int $featured = 0;

    public string $status = 'active';
}