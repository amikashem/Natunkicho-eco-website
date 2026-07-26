<?php

declare(strict_types=1);

namespace NKRecruitment\ATS\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Application
{
    public int $id = 0;
    public int $job_id = 0;
    public int $candidate_id = 0;
    public int $company_id = 0;
    public ?int $resume_id = null;
    
    public string $cover_letter = '';
    public string $status = 'new'; // 'new', 'screening', 'shortlisted', 'interview', 'offered', 'hired', 'rejected'
    
    public int $employer_rating = 0; // 0 to 5 stars
    public string $employer_notes = '';
}