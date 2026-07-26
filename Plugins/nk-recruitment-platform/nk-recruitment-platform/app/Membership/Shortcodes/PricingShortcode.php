<?php

declare(strict_types=1);

namespace NKRecruitment\Membership\Shortcodes;

use NKRecruitment\Membership\Services\PermissionService;
use NKRecruitment\Membership\Plans\PlanManager;

if (!defined('ABSPATH')) exit;

class PricingShortcode
{
    public function register(): void
    {
        add_shortcode('nk_pricing', [$this, 'render']);
    }

    public function render($atts = []): string
    {
        // 1. Extract your exact WooCommerce Product IDs
        $atts = shortcode_atts([
            'c_3m' => '2943', 'c_6m' => '2945', 'c_12m' => '2948', 'c_life' => '2950',
            'e_3m' => '2952', 'e_6m' => '2954', 'e_12m' => '2956', 'e_life' => '2956'
        ], $atts, 'nk_pricing');

        // 2. Determine Active Role View
        $user_id = get_current_user_id();
        $role = 'nkrp_candidate'; 
        
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('nkrp_employer', (array) $user->roles)) {
                $role = 'nkrp_employer';
            } elseif (in_array('administrator', (array) $user->roles)) {
                $role = (isset($_GET['view']) && $_GET['view'] === 'candidate') ? 'nkrp_candidate' : 'nkrp_employer';
            }
        } elseif (isset($_GET['view']) && $_GET['view'] === 'employer') {
            $role = 'nkrp_employer'; 
        }

        // 3. Check if user is already Premium
        $is_premium = false;
        if ($user_id > 0 && !in_array('administrator', (array) wp_get_current_user()->roles)) {
            $permService = new PermissionService();
            $type = ($role === 'nkrp_employer') ? 'employer' : 'candidate';
            $sub = $permService->getUserSubscription($user_id, $type);
            $is_premium = ($sub->plan_key ?? 'free') !== 'free';
        }

        // 4. Map the exact Shortcode IDs to the Packages
        if ($role === 'nkrp_employer') {
            $packages = [
                ['id' => $atts['e_3m'],   'title' => '3 Months',  'desc' => 'Short-term hiring & full CV access.', 'badge' => ''],
                ['id' => $atts['e_6m'],   'title' => '6 Months',  'desc' => 'Great for growing hospitality teams.', 'badge' => ''],
                ['id' => $atts['e_12m'],  'title' => '12 Months', 'desc' => 'Year-round active recruiting access.', 'badge' => 'Best Value'],
                ['id' => $atts['e_life'], 'title' => 'Lifetime',  'desc' => 'Pay once. Recruit top talent forever.', 'badge' => '']
            ];
        } else {
            $packages = [
                ['id' => $atts['c_3m'],   'title' => '3 Months',  'desc' => 'Profile boost for the current season.', 'badge' => ''],
                ['id' => $atts['c_6m'],   'title' => '6 Months',  'desc' => 'Ideal for active & serious job seekers.', 'badge' => ''],
                ['id' => $atts['c_12m'],  'title' => '12 Months', 'desc' => 'Year-round career growth & alerts.', 'badge' => 'Best Value'],
                ['id' => $atts['c_life'], 'title' => 'Lifetime',  'desc' => 'Lifetime access to all premium tools.', 'badge' => '']
            ];
        }

        $features = PlanManager::getFeatures($role);

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Membership/Views/frontend-pricing.php';
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div class="nkrp-notice">Error: Pricing template missing.</div>';
        }
        return ob_get_clean();
    }
}