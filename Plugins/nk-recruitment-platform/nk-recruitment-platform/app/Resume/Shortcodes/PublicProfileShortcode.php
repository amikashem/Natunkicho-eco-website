<?php

declare(strict_types=1);

namespace NKRecruitment\Resume\Shortcodes;

use NKRecruitment\Membership\Services\PermissionService;

if (!defined('ABSPATH')) {
    exit;
}

class PublicProfileShortcode
{
    public function register(): void
    {
        add_shortcode('nk_candidate_profile', [$this, 'render']);
    }

    public function render(array $atts = []): string
    {
        $resume_id = isset($_GET['resume_id']) ? (int) $_GET['resume_id'] : 0;

        if ($resume_id === 0) {
            return '<div class="nkrp-alert nkrp-alert-error">Resume not found.</div>';
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_resumes';
        $user_table = $wpdb->users;

        // Fetch without strict status check first
        $resume_data = $wpdb->get_row($wpdb->prepare("
            SELECT r.*, u.display_name, u.user_email 
            FROM {$table} r 
            JOIN {$user_table} u ON r.user_id = u.ID 
            WHERE r.id = %d
        ", $resume_id));

        if (!$resume_data) {
            return '<div class="nkrp-alert nkrp-alert-error">This profile does not exist.</div>';
        }

        $viewer_id = get_current_user_id();
        $candidate_user_id = (int) $resume_data->user_id;

        // 1. Initialize the Gatekeeper
        $permissionService = new PermissionService();
        $is_admin = in_array('administrator', (array) wp_get_current_user()->roles);
        
        // Ensure CV is active, unless viewer is Admin or the Owner
        if ($resume_data->status !== 'active' && !$is_admin && $viewer_id !== $candidate_user_id) {
            return '<div class="nkrp-alert nkrp-alert-error">This profile is currently private.</div>';
        }

        // 2. Decide if the viewer gets the "God Mode" view or the "Blurred" view
        // Checks: Are they Admin? Premium? OR Free Employer who received an application from this exact candidate?
        $is_unlocked = $is_admin || $viewer_id === $candidate_user_id || $permissionService->canViewCandidateContact($viewer_id, $candidate_user_id);

        // 3. Decode JSON arrays for the View
        $experience = json_decode((string)$resume_data->experience_data, true) ?: [];
        $education = json_decode((string)$resume_data->education_data, true) ?: [];
        $skills = json_decode((string)$resume_data->skills_data, true) ?: [];

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Resume/Views/frontend-public-profile.php';
        
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div class="nkrp-alert nkrp-alert-error">Profile template missing.</div>';
        }
        
        return ob_get_clean();
    }
}