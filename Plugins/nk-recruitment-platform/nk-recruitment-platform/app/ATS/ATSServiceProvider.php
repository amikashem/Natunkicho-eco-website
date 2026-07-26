<?php

declare(strict_types=1);

namespace NKRecruitment\ATS;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\ATS\Shortcodes\ApplyJobShortcode;
// NEW: Import the Employer ATS Dashboard
use NKRecruitment\ATS\Shortcodes\EmployerATSShortcode;

if (!defined('ABSPATH')) {
    exit;
}

class ATSServiceProvider extends ServiceProvider
{
    /**
     * Register the ATS Module services and shortcodes.
     */
    public function register(): void
    {
        // 1. Boot up the Frontend Apply Job Shortcode (Candidate Side)
        (new ApplyJobShortcode())->register();

        // 2. Boot up the Employer ATS Dashboard (Employer Side)
        (new EmployerATSShortcode())->register();
    }
}