<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

use NKRecruitment\Admin\AdminServiceProvider;
use NKRecruitment\Jobs\JobServiceProvider;
use NKRecruitment\Employer\EmployerServiceProvider;
use NKRecruitment\Candidate\CandidateServiceProvider; 
use NKRecruitment\Resume\ResumeServiceProvider; 
use NKRecruitment\ATS\ATSServiceProvider; // NEW: Import ATS Provider
use NKRecruitment\Media\MediaServiceProvider;
use NKRecruitment\Search\SearchServiceProvider;
use NKRecruitment\Notifications\NotificationServiceProvider;
use NKRecruitment\Membership\MembershipServiceProvider;
use NKRecruitment\AI\AIServiceProvider;
use NKRecruitment\Analytics\AnalyticsServiceProvider;

// Core Components
use NKRecruitment\Core\Router;
use NKRecruitment\Auth\Controllers\AuthController; 
use NKRecruitment\Jobs\Shortcodes\PostJobShortcode;

if (!defined('ABSPATH')) {
    exit;
}

class Application
{
    public function boot(): void
    {
        // 1. Boot up the Core Router to intercept custom SEO URLs
        (new Router())->register();

        // 2. Boot up the Authentication System (Login/Register/Roles)
        (new AuthController())->register();

        // 3. Load all modular Service Providers
        $loader = new Loader();

        $loader
            ->add(new AdminServiceProvider())
            ->add(new JobServiceProvider())
            ->add(new EmployerServiceProvider())
            ->add(new CandidateServiceProvider()) 
            ->add(new ResumeServiceProvider()) 
            ->add(new ATSServiceProvider()) // NEW: Plug in the ATS Module!
            ->add(new MediaServiceProvider())
            ->add(new NotificationServiceProvider())
            ->add(new MembershipServiceProvider())
            ->add(new AIServiceProvider())
            ->add(new AnalyticsServiceProvider())
            ->add(new SearchServiceProvider());

        $loader->run();
    }
}