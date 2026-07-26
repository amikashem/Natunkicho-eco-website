<?php

declare(strict_types=1);

namespace NKRecruitment\Notifications\Queue;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Notifications\Email\EmailService;

if (!defined('ABSPATH')) {
    exit;
}

class EmailQueueService
{
    private \wpdb $db;
    private EmailService $emailService;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->emailService = new EmailService();
    }

    /**
     * Pushes a low-priority email into the database queue instead of sending it instantly.
     */
    public function pushToQueue(string $to, string $subject, string $message): bool
    {
        $table = DatabaseManager::table('email_queue');
        
        $inserted = $this->db->insert($table, [
            'recipient_email' => $to,
            'subject'         => $subject,
            'body'            => $message,
            'status'          => 'pending',
            'created_at'      => current_time('mysql')
        ]);

        return $inserted !== false;
    }

    /**
     * Processes a safe batch of emails. 
     * This will be triggered by a 5-minute background Cron Job.
     */
    public function processBatch(int $batchSize = 50): int
    {
        $table = DatabaseManager::table('email_queue');

        // 1. Grab pending emails securely (FIFO - First In, First Out)
        $emails = $this->db->get_results($this->db->prepare(
            "SELECT id, recipient_email, subject, body FROM {$table} WHERE status = 'pending' AND attempts < 3 ORDER BY id ASC LIMIT %d",
            $batchSize
        ));

        if (empty($emails)) {
            return 0; // Nothing to process
        }

        $sentCount = 0;

        foreach ($emails as $email) {
            // 2. Attempt to send using our beautiful HTML Email Service
            $success = $this->emailService->send($email->recipient_email, $email->subject, $email->body);

            // 3. Update the database record
            if ($success) {
                $this->db->update(
                    $table,
                    ['status' => 'sent', 'sent_at' => current_time('mysql')],
                    ['id' => $email->id]
                );
                $sentCount++;
            } else {
                // If it fails, increment attempts so it tries again next time, but eventually stops (Max 3)
                $this->db->query($this->db->prepare(
                    "UPDATE {$table} SET attempts = attempts + 1, status = IF(attempts >= 2, 'failed', 'pending') WHERE id = %d",
                    $email->id
                ));
            }
        }

        return $sentCount;
    }
}