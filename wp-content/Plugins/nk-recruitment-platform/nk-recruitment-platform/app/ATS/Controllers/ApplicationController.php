<?php

declare(strict_types=1);

namespace NKRecruitment\ATS\Controllers;

use NKRecruitment\ATS\Models\Application;
use NKRecruitment\ATS\Services\ApplicationService;
use NKRecruitment\Notifications\Email\EmailService; // <-- Added Email Service

if (!defined('ABSPATH')) {
    exit;
}

class ApplicationController
{
    private ApplicationService $service;
    private EmailService $emailService;

    public function __construct()
    {
        $this->service = new ApplicationService();
        $this->emailService = new EmailService(); // <-- Instantiate it
    }

    // Fast lookup for dropdown menus
    private function getLookupData(string $table, string $id_col, string $label_col): array
    {
        global $wpdb;
        return $wpdb->get_results("SELECT {$id_col} as id, {$label_col} as label FROM {$wpdb->prefix}nkrp_{$table} ORDER BY id DESC LIMIT 200") ?: [];
    }

    public function applicationList(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['application_ids'])) {
            check_admin_referer('bulk-applications', 'bulk_applications_nonce');
            $action = sanitize_text_field($_POST['action'] ?? '-1');
            $ids = array_map('intval', $_POST['application_ids']);

            if (in_array($action, ['new', 'screening', 'interview', 'offered', 'hired', 'rejected'])) {
                $this->service->bulkUpdateStatus($ids, $action);
                // Note: Bulk emailing will be handled by the Background Queue module later to prevent PHP timeouts!
            } elseif ($action === 'trash') {
                $this->service->bulkDelete($ids);
            }
            wp_redirect(admin_url('admin.php?page=nkrp-applications&msg=updated'));
            exit;
        }

        $status = sanitize_text_field($_GET['status'] ?? '');
        $paged  = max(1, (int) ($_GET['paged'] ?? 1));
        $limit  = 15;
        $args   = ['status' => $status, 'limit' => $limit, 'offset' => ($paged - 1) * $limit];

        $applications = $this->service->getApplications($args);
        $total_items  = $this->service->countApplications($args);
        $total_pages  = ceil($total_items / $limit);

        $counts = [
            'all' => $this->service->countApplications(),
            'new' => $this->service->countApplications(['status' => 'new']),
            'interview' => $this->service->countApplications(['status' => 'interview']),
            'hired' => $this->service->countApplications(['status' => 'hired']),
            'rejected' => $this->service->countApplications(['status' => 'rejected']),
        ];

        require NKRP_PLUGIN_PATH . 'app/ATS/Views/application-list.php';
    }

    public function applicationCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_application');
            $app = new Application();
            $app->job_id       = (int) $_POST['job_id'];
            $app->candidate_id = (int) $_POST['candidate_id'];
            $app->company_id   = (int) $_POST['company_id'];
            $app->cover_letter = sanitize_textarea_field($_POST['cover_letter'] ?? '');
            $app->status       = 'new';
            $this->service->create($app);
            wp_redirect(admin_url('admin.php?page=nkrp-applications&msg=created'));
            exit;
        }

        $jobs = $this->getLookupData('jobs', 'id', 'job_title');
        $candidates = $this->getLookupData('candidates', 'id', 'CONCAT(first_name, " ", last_name)');
        $companies = $this->getLookupData('companies', 'id', 'company_name');
        
        require NKRP_PLUGIN_PATH . 'app/ATS/Views/application-create.php';
    }

    public function applicationEdit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $application = $this->service->find($id);
        
        if (!$application) {
            wp_die(__('Application not found.', 'nk-recruitment'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_application');
            
            $new_status = sanitize_text_field($_POST['status']);
            
            $app = new Application();
            $app->id              = $id;
            $app->status          = $new_status;
            $app->employer_rating = (int) $_POST['employer_rating'];
            $app->employer_notes  = sanitize_textarea_field($_POST['employer_notes'] ?? '');
            
            $this->service->update($app);

            // ---------------------------------------------------------
            // THE NOTIFICATION TRIGGER
            // If the status changed, notify the candidate!
            // ---------------------------------------------------------
            if ($application->status !== $new_status) {
                $this->notifyCandidate($application, $new_status);
            }

            wp_redirect(admin_url('admin.php?page=nkrp-applications&msg=updated'));
            exit;
        }
        
        require NKRP_PLUGIN_PATH . 'app/ATS/Views/application-edit.php';
    }

    public function applicationDelete(): void
    {
        if (isset($_GET['id'])) {
            $this->service->delete((int) $_GET['id']);
            wp_redirect(admin_url('admin.php?page=nkrp-applications&msg=deleted'));
            exit;
        }
    }

    // =====================================================
    // NOTIFICATION LOGIC
    // =====================================================
    private function notifyCandidate(object $application, string $new_status): void
    {
        global $wpdb;
        
        // Fetch candidate email (Since the application repo only pulled the name)
        $email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}nkrp_candidates WHERE id = %d", $application->candidate_id));
        
        if (!$email) return; // Silent fail if no email exists

        $job_title = esc_html($application->job_title);
        $company   = esc_html($application->company_name);
        $name      = esc_html($application->candidate_name);

        $subject = '';
        $message = '';

        switch ($new_status) {
            case 'interview':
                $subject = "Interview Request: {$job_title} at {$company}";
                $message = "<h3>Great news, {$name}!</h3><p>Your application for the <strong>{$job_title}</strong> position has been successfully reviewed, and <strong>{$company}</strong> would like to invite you to an interview.</p><p>The employer will be in touch shortly with schedule details.</p>";
                break;
            case 'offered':
                $subject = "Job Offer: {$job_title} at {$company}";
                $message = "<h3>Congratulations, {$name}!</h3><p><strong>{$company}</strong> has decided to extend a formal job offer for the <strong>{$job_title}</strong> position.</p><p>Please check your direct communications for the offer letter details.</p>";
                break;
            case 'hired':
                $subject = "You're Hired! Welcome to {$company}";
                $message = "<h3>Amazing job, {$name}!</h3><p>You have officially been marked as Hired for the <strong>{$job_title}</strong> role at <strong>{$company}</strong>.</p><p>We wish you the best of luck in your new hospitality career!</p>";
                break;
            case 'rejected':
                $subject = "Application Update: {$job_title}";
                $message = "<h3>Hello {$name},</h3><p>Thank you for applying to the <strong>{$job_title}</strong> role at <strong>{$company}</strong>.</p><p>While your profile was impressive, the employer has decided to move forward with other candidates at this time. Keep applying! There are many other great hospitality roles waiting for you.</p>";
                break;
        }

        // Only send if we have a defined message for this specific status
        if (!empty($message)) {
            $this->emailService->send($email, $subject, $message);
        }
    }
}