<?php

declare(strict_types=1);

namespace NKRecruitment\Notifications;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Notifications\Email\EmailService;
// Note: We will use EmailQueueService for bulk items later if needed!

if (!defined('ABSPATH')) {
    exit;
}

class NotificationService
{
    private \wpdb $db;
    private EmailService $emailService;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->emailService = new EmailService();
    }

    /**
     * Send a Two-Way Alert (Dashboard + Email)
     *
     * @param int $user_id The WordPress User ID receiving the alert
     * @param string $title Short title for the dashboard
     * @param string $message Full message body
     * @param string $type 'info', 'success', or 'warning'
     * @param string|null $action_url A link they can click in the dashboard
     * @param bool $send_email Whether to trigger an email too
     * @param bool $is_bulk TRUE if this is part of a bulk blast (prevents WP fallback)
     */
    public function notify(
        int $user_id, 
        string $title, 
        string $message, 
        string $type = 'info', 
        ?string $action_url = null,
        bool $send_email = true,
        bool $is_bulk = false
    ): void {
        
        // 1. SAVE TO DASHBOARD (Database)
        $table = DatabaseManager::table('notifications');
        $this->db->insert($table, [
            'user_id'    => $user_id,
            'title'      => sanitize_text_field($title),
            'message'    => wp_kses_post($message),
            'type'       => sanitize_text_field($type),
            'action_url' => $action_url ? esc_url_raw($action_url) : null,
        ]);

        // 2. SEND THE EMAIL 
        if ($send_email) {
            $user = get_userdata($user_id);
            if ($user && is_email($user->user_email)) {
                
                // Add the action button to the email body if a URL exists
                $email_body = $message;
                if ($action_url) {
                    $email_body .= "<br><br><a href='{$action_url}' style='display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>View Details</a>";
                }

                // Send it through our newly updated SES-ready EmailService!
                $this->emailService->send($user->user_email, $title, $email_body, $is_bulk);
            }
        }
    }
}