<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Candidate
{
    public int $id = 0;
    public int $user_id = 0;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $whatsapp_number = ''; // NEW: Added for WhatsApp Integration
    public string $professional_title = '';
    public string $location_city = '';
    public string $location_country = '';
    public ?string $date_of_birth = null;
    public string $gender = '';
    public string $nationality = '';
    public float $current_salary = 0.00;
    public float $expected_salary = 0.00;
    public string $salary_currency = 'USD';
    public int $experience_years = 0;
    public string $education_level = '';
    public string $availability = '';
    public string $bio = '';
    public string $skills = '';
    public string $languages = '';
    public string $linkedin_url = '';
    public string $portfolio_url = '';
    public int $profile_photo_id = 0;
    public int $resume_file_id = 0;
    public int $is_featured = 0;
    public string $status = 'active';
    public int $profile_views = 0;
}