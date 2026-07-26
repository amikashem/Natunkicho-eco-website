<?php

declare(strict_types=1);

namespace NKRecruitment\Notifications;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Notifications\Queue\EmailQueueService;

if (!defined('ABSPATH')) {
    exit;
}

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Notification Service hooks
    (new \NKRecruitment\Notifications\Services\NotificationService())->registerHooks();
        
        // 1. Register the custom 5-minute time interval
        add_filter('cron_schedules', [$this, 'addCronSchedules']);

        // 2. Safely schedule the Cron Job (runs once on boot to ensure it's registered)
        add_action('init', [$this, 'scheduleQueueWorker']);

        // 3. Hook the actual worker function to the scheduled event
        add_action('nkrp_process_email_queue', [$this, 'processQueueWorker']);
        
        // Listen for the welcome email action triggered after registration
        add_action('nkrp_send_welcome_email', function (int $user_id, string $email, string $verify_token) {
            // Resolve the EmailService (assuming you use a container, or simply instantiate it)
            $emailService = new \NKRecruitment\Notifications\Email\EmailService();
            
            $site_name = get_bloginfo('name');
            $subject = sprintf(__('Welcome to %s! Please verify your email', 'nk-recruitment'), $site_name);
            
            // Construct the secure verification link
            $verify_url = add_query_arg([
                'nkrp_verify' => $verify_token,
                'uid'         => $user_id
            ], home_url('/')); // e.g. https://yoursite.com/?nkrp_verify=xyz&uid=123
        
            // Build the SaaS-style email body using your template's CSS classes
            $message = '<h2>' . __('Welcome aboard!', 'nk-recruitment') . '</h2>';
            $message .= '<p>' . sprintf(__('Thanks for joining %s. We are thrilled to have you.', 'nk-recruitment'), $site_name) . '</p>';
            $message .= '<p>' . __('To get started and unlock all features—like publishing your profile or posting jobs—please verify your email address by clicking the button below:', 'nk-recruitment') . '</p>';
            
            // The .email-button class is already defined in your default-email.php
            $message .= '<p style="text-align: center;">';
            $message .= '<a href="' . esc_url($verify_url) . '" class="email-button">' . __('Verify My Email', 'nk-recruitment') . '</a>';
            $message .= '</p>';
            
            $message .= '<p>' . __('If the button doesn\'t work, you can copy and paste this link into your browser:', 'nk-recruitment') . '</p>';
            $message .= '<p style="word-break: break-all; color: #64748b; font-size: 13px;">' . esc_url($verify_url) . '</p>';
        
            // Send the email (not bulk, so fallback to wp_mail is allowed)
            $emailService->send($email, $subject, $message, false);
        }, 10, 3);
            }

    /**
     * Adds a custom "Every 5 Minutes" schedule to WordPress.
     */
    public function addCronSchedules(array $schedules): array
    {
        $schedules['nkrp_five_minutes'] = [
            'interval' => 300, // 300 seconds = 5 minutes
            'display'  => __('Every 5 Minutes (NK Recruitment)', 'nk-recruitment')
        ];
        return $schedules;
    }

    /**
     * Checks if the job is scheduled, and if not, schedules it.
     */
    public function scheduleQueueWorker(): void
    {
        if (!wp_next_scheduled('nkrp_process_email_queue')) {
            wp_schedule_event(time(), 'nkrp_five_minutes', 'nkrp_process_email_queue');
        }
    }

    /**
     * The actual function that runs in the background every 5 minutes.
     * It spins up the Queue Service and processes a safe batch of 50 emails.
     */
    public function processQueueWorker(): void
    {
        $queue = new EmailQueueService();
        $queue->processBatch(50); // Processes max 50 emails per 5 mins (600/hour)
    }
}