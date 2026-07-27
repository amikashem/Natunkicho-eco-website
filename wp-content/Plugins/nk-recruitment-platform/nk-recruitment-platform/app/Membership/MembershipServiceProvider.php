<?php

declare(strict_types=1);

namespace NKRecruitment\Membership;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Membership\Services\PermissionService;
use NKRecruitment\Membership\Shortcodes\PricingShortcode;
use NKRecruitment\Membership\Admin\MembershipAdmin;
use NKRecruitment\Membership\Services\WooCommerceIntegration;
use NKRecruitment\Membership\Services\MembershipCronService; // Added the Cron Service import!

if (!defined('ABSPATH')) {
    exit;
}

class MembershipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auto-assign free plan on registration
        add_action('user_register', [$this, 'assignDefaultPlan'], 10, 1);

        // Register the Frontend Pricing Table
        (new PricingShortcode())->register();

        // Register the WooCommerce Auto-Upgrader
        (new WooCommerceIntegration())->register();

        // Register the Background Cron Auto-Expirations
        (new MembershipCronService())->register(); // Boot up the Cron Job!

        // Register the Admin Manual Override Tool
        if (is_admin()) {
            (new MembershipAdmin())->register();
        }
    }

    public function assignDefaultPlan(int $user_id): void
    {
        $permissionService = new PermissionService();
        $role = sanitize_text_field($_POST['role'] ?? 'candidate');
        $permissionService->getUserSubscription($user_id, $role);
    }
}