<?php

declare(strict_types=1);

namespace NKRecruitment\Membership\Services;

if (!defined('ABSPATH')) exit;

class MembershipCronService
{
    public function register(): void
    {
        // 1. Schedule the event if it isn't scheduled already
        if (!wp_next_scheduled('nkrp_daily_membership_check')) {
            wp_schedule_event(time(), 'daily', 'nkrp_daily_membership_check');
        }

        // 2. Hook our function to the scheduled event
        add_action('nkrp_daily_membership_check', [$this, 'processMemberships']);
    }

    /**
     * This function runs once a day automatically in the background.
     */
    public function processMemberships(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_subscriptions';

        // Safety check to ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return;
        }

        $now = current_time('mysql');

        // ==========================================
        // A. DOWNGRADE EXPIRED MEMBERSHIPS
        // ==========================================
        // Excludes Lifetime (where expires_at is null or 0000-00-00)
        $expired_subs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE status = 'active' 
             AND expires_at IS NOT NULL 
             AND expires_at != '0000-00-00 00:00:00' 
             AND expires_at <= %s",
            $now
        ));

        if (!empty($expired_subs)) {
            foreach ($expired_subs as $sub) {
                // 1. Update status in database to instantly lock premium features
                $wpdb->update(
                    $table,
                    ['status' => 'expired'],
                    ['id' => $sub->id],
                    ['%s'],
                    ['%d']
                );

                // 2. Fire the global hook so Phase 7 (NK Email Engine) can send the "Membership Expired" email later
                do_action('nkrp_membership_expired', $sub->user_id, $sub->plan_key);
            }
        }

        // ==========================================
        // B. FIRE RENEWAL REMINDERS (7 Days, 3 Days, 1 Day)
        // ==========================================
        $reminder_intervals = [7, 3, 1];
        
        foreach ($reminder_intervals as $days) {
            // Calculate the exact target day window
            $target_start = date('Y-m-d 00:00:00', strtotime("+{$days} days", current_time('timestamp')));
            $target_end = date('Y-m-d 23:59:59', strtotime("+{$days} days", current_time('timestamp')));

            $expiring_soon = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} 
                 WHERE status = 'active' 
                 AND expires_at >= %s AND expires_at <= %s",
                $target_start,
                $target_end
            ));

            if (!empty($expiring_soon)) {
                foreach ($expiring_soon as $sub) {
                    // Fire global hook for pre-expiry emails
                    do_action('nkrp_membership_expiring_soon', $sub->user_id, $sub->plan_key, $days);
                }
            }
        }
    }
}