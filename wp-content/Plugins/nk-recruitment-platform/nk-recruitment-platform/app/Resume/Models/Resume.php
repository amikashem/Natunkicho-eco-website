<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Models;

if (!defined('ABSPATH')) {
    exit;
}

class Resume
{
    public int $id = 0;
    public int $candidate_id = 0;
    public int $user_id = 0;
    public string $resume_title = '';
    public string $objective = '';
    
    // We store these as JSON strings in the database, 
    // but the Controller will pass them as arrays and encode/decode them.
    public string $education_data = '[]';
    public string $experience_data = '[]';
    public string $skills_data = '[]';
    public string $certifications_data = '[]';
    public string $languages_data = '[]';
    public string $portfolio_data = '[]';
    public string $ai_parsed_data = '{}';
    
    public ?string $file_path = null;
    public string $file_type = 'manual'; // 'manual', 'pdf_upload', 'ai_generated'
    public int $is_primary = 0;
    public string $status = 'active';
}