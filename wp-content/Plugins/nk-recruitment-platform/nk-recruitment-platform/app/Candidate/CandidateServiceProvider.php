<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Candidate\Shortcodes\CandidateDashboardShortcode;

if (!defined('ABSPATH')) {
    exit;
}

class CandidateServiceProvider extends ServiceProvider
{
    // =====================================================
    // SECTION 1: Register Module
    // =====================================================

    public function register(): void
    {
        // 1. Register Candidate Frontend Shortcodes
        (new CandidateDashboardShortcode())->register();
        
        // (In the future, we will add Resume and Application logic here)
    }
}