<?php

declare(strict_types=1);

namespace NKRecruitment\Membership\Plans;

if (!defined('ABSPATH')) exit;

class PlanManager
{
    /**
     * Defines the dynamic feature lists for the Pricing Tables
     * Format: 'Feature Name' => [Free Has It?, Premium Has It?]
     */
    public static function getFeatures(string $role): array
    {
        if ($role === 'nkrp_employer') {
            return [
                'Basic Dashboard Access' => [true, true],
                'Post Active Jobs' => [true, true],
                'Receive Applications' => [true, true],
                'Blurred Talent View' => [true, true],
                'Instant CV Downloads' => [false, true],
                'View Full Names & Contact Info' => [false, true],
                'Direct Candidate Messaging' => [false, true],
                'Verified Employer Badge' => [false, true],
            ];
        }

        if ($role === 'nkrp_candidate') {
            return [
                'Basic Dashboard Access' => [true, true],
                'Standard Job Applications' => [true, true],
                'AI Resume Builder (1 Resume)' => [true, true],
                'Priority Application Status' => [false, true],
                'Unlimited AI Resumes' => [false, true],
                'Instant Job Alerts' => [false, true],
                'Profile View Analytics' => [false, true],
            ];
        }

        return [];
    }

    /**
     * Fetches the user's active subscription details from the custom table.
     * This allows us to get the exact Plan ID and Expiry Date.
     */
    public static function getUserSubscription(int $user_id): ?object
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_subscriptions';
        
        // Safety check to ensure the table exists before querying
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return null;
        }

        // Get the active subscription row 
        // We assume your table uses 'status' and 'plan_key' columns as discussed in your architecture
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d AND status = 'active' ORDER BY id DESC LIMIT 1",
            $user_id
        ));

        return $subscription;
    }
}