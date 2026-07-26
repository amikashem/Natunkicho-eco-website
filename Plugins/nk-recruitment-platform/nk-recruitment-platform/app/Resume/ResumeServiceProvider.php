<?php

declare(strict_types=1);

namespace NKRecruitment\Resume;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Resume\Shortcodes\CreateResumeShortcode;
use NKRecruitment\Resume\Shortcodes\EditResumeShortcode;
use NKRecruitment\Resume\Shortcodes\PublicProfileShortcode;

if (!defined('ABSPATH')) {
    exit;
}

class ResumeServiceProvider extends ServiceProvider
{
    /**
     * Register the Resume Module services and shortcodes.
     */
    public function register(): void
    {
        // Register the Frontend Resume Builder
        (new CreateResumeShortcode())->register();
        
        // NEW: Register the Frontend Resume Editor & Uploader
        (new EditResumeShortcode())->register();
        (new PublicProfileShortcode())->register();
    }
}