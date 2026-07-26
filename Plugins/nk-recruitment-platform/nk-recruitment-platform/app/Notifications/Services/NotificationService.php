<?php

declare(strict_types=1);

namespace NKRecruitment\Notifications\Services;

if (!defined('ABSPATH')) {
    exit;
}

class NotificationService
{
    public function registerHooks(): void
    {
        // -----------------------------------------------------
        // CANDIDATE EVENTS
        // -----------------------------------------------------
        add_action('nkrp_candidate_registered', [$this, 'sendCandidateWelcome'], 10, 1);
        add_action('nkrp_candidate_applied', [$this, 'sendApplicationReceived'], 10, 2);
        
        // -----------------------------------------------------
        // EMPLOYER EVENTS
        // -----------------------------------------------------
        add_action('nkrp_job_submitted', [$this, 'sendJobSubmitted'], 10, 1);
        add_action('nkrp_job_approved', [$this, 'sendJobApproved'], 10, 1);
        
        // -----------------------------------------------------
        // SYSTEM / PREMIUM EVENTS
        // -----------------------------------------------------
        add_action('nkrp_premium_purchased', [$this, 'sendPremiumWelcome'], 10, 2);
    }

    /**
     * Helper to safely pass data to the NK Email Engine
     * We wrap this so if the Engine is disabled, it doesn't crash the plugin.
     */
    private function triggerNKEmailEngine(string $to_email, string $template_id, array $data): void
    {
        // Check if the NK Email Engine is active
        if (has_action('nk_email_send_event')) {
            do_action('nk_email_send_event', $to_email, $template_id, $data);
        } else {
            // Fallback: If engine is missing, log the event for debugging
            error_log("NKRP Notification Fallback: Would have sent '$template_id' to '$to_email'");
        }
    }

    public function sendCandidateWelcome(int $user_id): void
    {
        $user = get_userdata($user_id);
        if (!$user) return;

        // Check if user disabled 'Promotional Emails'
        $wants_promo = get_user_meta($user_id, '_nkrp_pref_promo_emails', true) !== 'no';
        if (!$wants_promo) return;

        $this->triggerNKEmailEngine($user->user_email, 'candidate_welcome', [
            'first_name' => $user->first_name,
            'login_url'  => home_url('/login/')
        ]);
    }

    public function sendApplicationReceived(int $user_id, int $job_id): void
    {
        $candidate = get_userdata($user_id);
        if (!$candidate) return;

        // Fetch Job and Employer Details
        global $wpdb;
        $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nkrp_jobs WHERE id = %d", $job_id));
        if (!$job) return;

        $employer = get_userdata((int)$job->user_id);

        // 1. Send confirmation to Candidate
        $this->triggerNKEmailEngine($candidate->user_email, 'application_submitted', [
            'candidate_name' => $candidate->first_name,
            'job_title'      => $job->job_title,
            'dashboard_url'  => home_url('/candidate-dashboard/?tab=applied-jobs')
        ]);

        // 2. Send alert to Employer (If they have notifications enabled)
        if ($employer) {
            $wants_alerts = get_user_meta($employer->ID, '_nkrp_pref_employer_notifications', true) !== 'no';
            
            if ($wants_alerts) {
                $this->triggerNKEmailEngine($employer->user_email, 'new_application_received', [
                    'employer_name'  => $employer->first_name,
                    'job_title'      => $job->job_title,
                    'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                    'ats_url'        => home_url('/employer-dashboard/?tab=ats')
                ]);
            }
        }
    }

    public function sendJobSubmitted(int $job_id): void
    {
        global $wpdb;
        $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nkrp_jobs WHERE id = %d", $job_id));
        if (!$job) return;

        $employer = get_userdata((int)$job->user_id);
        if (!$employer) return;

        $wants_alerts = get_user_meta($employer->ID, '_nkrp_pref_employer_notifications', true) !== 'no';
        if ($wants_alerts) {
            $this->triggerNKEmailEngine($employer->user_email, 'job_pending_approval', [
                'employer_name' => $employer->first_name,
                'job_title'     => $job->job_title,
            ]);
        }
    }

    public function sendJobApproved(int $job_id): void
    {
        global $wpdb;
        $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nkrp_jobs WHERE id = %d", $job_id));
        if (!$job) return;

        $employer = get_userdata((int)$job->user_id);
        if (!$employer) return;

        $wants_alerts = get_user_meta($employer->ID, '_nkrp_pref_employer_notifications', true) !== 'no';
        if ($wants_alerts) {
            $this->triggerNKEmailEngine($employer->user_email, 'job_approved_live', [
                'employer_name' => $employer->first_name,
                'job_title'     => $job->job_title,
                'job_url'       => home_url('/job-details/?id=' . $job->id)
            ]);
        }
    }

    public function sendPremiumWelcome(int $user_id, string $plan_name): void
    {
        $user = get_userdata($user_id);
        if (!$user) return;

        $this->triggerNKEmailEngine($user->user_email, 'premium_activated', [
            'user_name' => $user->first_name,
            'plan_name' => $plan_name,
        ]);
    }
}