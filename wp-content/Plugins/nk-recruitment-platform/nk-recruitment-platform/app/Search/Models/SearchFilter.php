<?php

declare(strict_types=1);

namespace NKRecruitment\Search\Models;

if (!defined('ABSPATH')) {
    exit;
}

class SearchFilter
{
    public string $keyword = '';
    public string $location = '';
    
    // Pagination
    public int $page = 1;
    public int $limit = 15;
    public int $offset = 0;
    
    // Specific Filters
    public string $job_type = '';
    public string $workplace_type = '';
    public string $experience_level = '';
    public string $industry = '';
    
    public int $min_salary = 0;
    public int $min_experience = 0;
    
    // Booleans
    public bool $featured_only = false;
    public bool $verified_only = false;
    
    public string $orderby = 'created_at DESC';

    /**
     * Instantiates and sanitizes raw input arrays (like $_GET) securely.
     */
    public static function fromArray(array $data): self
    {
        $filter = new self();
        
        $filter->keyword          = sanitize_text_field($data['keyword'] ?? '');
        $filter->location         = sanitize_text_field($data['location'] ?? '');
        $filter->job_type         = sanitize_text_field($data['job_type'] ?? '');
        $filter->workplace_type   = sanitize_text_field($data['workplace_type'] ?? '');
        $filter->experience_level = sanitize_text_field($data['experience_level'] ?? '');
        $filter->industry         = sanitize_text_field($data['industry'] ?? '');
        
        $filter->min_salary       = absint($data['min_salary'] ?? 0);
        $filter->min_experience   = absint($data['min_experience'] ?? 0);
        
        $filter->featured_only    = !empty($data['featured']);
        $filter->verified_only    = !empty($data['verified']);

        // DDoS Pagination Protection (Max 100 items per request)
        $limit = max(1, absint($data['limit'] ?? 15));
        $filter->limit = min($limit, 100);
        $filter->page = max(1, absint($data['page'] ?? 1));
        $filter->offset = ($filter->page - 1) * $filter->limit;

        return $filter;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}