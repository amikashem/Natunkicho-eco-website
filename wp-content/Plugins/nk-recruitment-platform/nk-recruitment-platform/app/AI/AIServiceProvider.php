<?php

declare(strict_types=1);

namespace NKRecruitment\AI;

use NKRecruitment\Core\ServiceProvider;

if (!defined('ABSPATH')) {
    exit;
}

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Future Webhooks or AJAX actions for frontend AI generation will be registered here.
        // Example: add_action('wp_ajax_nkrp_generate_jd', [...]);
    }
}