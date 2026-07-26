<?php

declare(strict_types=1);

namespace NKRecruitment\Membership\Services;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\Membership\Plans\PlanManager;

if (!defined('ABSPATH')) {
    exit;
}

class PermissionService
{
    private \wpdb $db;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
    }

    public function getUserSubscription(int $user_id, string $type = 'employer'): object
    {
        $table = DatabaseManager::table('subscriptions');
        $sub = $this->db->get_row($this->db->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d AND status = 'active' ORDER BY id DESC LIMIT 1",
            $user_id
        ));

        if (!$sub) {
            $this->db->insert($table, [
                'user_id' => $user_id,
                'user_type' => $type,
                'plan_key' => 'free',
                'started_at' => current_time('mysql') 
            ]);
            
            return (object) [
                'plan_key' => 'free',
                'jobs_posted' => 0,
                'applications_viewed' => 0
            ];
        }

        return $sub;
    }

    public function canPostJob(int $user_id): bool
    {
        $sub = $this->getUserSubscription($user_id, 'employer');
        $limits = PlanManager::getPlanLimits($sub->plan_key, 'employer');
        return (int) $sub->jobs_posted < $limits['max_jobs'];
    }

    public function canDownloadResume(int $user_id): bool
    {
        $sub = $this->getUserSubscription($user_id, 'employer');
        $limits = PlanManager::getPlanLimits($sub->plan_key, 'employer');
        return $limits['can_download_cv'] === true;
    }

    public function incrementJobCount(int $user_id): void
    {
        $table = DatabaseManager::table('subscriptions');
        $this->db->query($this->db->prepare(
            "UPDATE {$table} SET jobs_posted = jobs_posted + 1 WHERE user_id = %d AND status = 'active'",
            $user_id
        ));
    }

    public function canCreateResume(int $user_id): bool
    {
        $sub = $this->getUserSubscription($user_id, 'candidate');
        $limit = ($sub->plan_key === 'free') ? 1 : 5;
        $table = DatabaseManager::table('resumes');
        $count = (int) $this->db->get_var($this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id));
        return $count < $limit;
    }

    // =========================================================================
    // PHASE 4 & 5 LOGIC: VISIBILITY & SEARCH
    // =========================================================================

    /**
     * Checks if a viewer can see a specific Candidate's unblurred contact details & CV.
     */
    public function canViewCandidateContact(int $viewer_id, int $candidate_user_id = 0): bool
    {
        if ($viewer_id === 0) return false; // Guests cannot view full profiles
        
        if (user_can($viewer_id, 'manage_options')) return true; // Admins see everything

        $user = get_userdata($viewer_id);
        if (!$user) return false;

        // EMPLOYER LOGIC
        if (in_array('nkrp_employer', (array) $user->roles)) {
            $sub = $this->getUserSubscription($viewer_id, 'employer');
            if ($sub->plan_key !== 'free') return true; // Premium Employers see everything
            
            // Free Employers: Can ONLY see if the candidate applied to their job
            if ($candidate_user_id > 0) {
                global $wpdb;
                $apps_table = $wpdb->prefix . 'nkrp_applications';
                $jobs_table = $wpdb->prefix . 'nkrp_jobs';
                
                // BUG FIX: Removed j.employer_id, using only j.user_id
                $has_applied = $wpdb->get_var($wpdb->prepare("
                    SELECT a.id FROM {$apps_table} a
                    JOIN {$jobs_table} j ON a.job_id = j.id
                    WHERE a.candidate_id = %d AND j.user_id = %d
                    LIMIT 1
                ", $candidate_user_id, $viewer_id));
                
                return !empty($has_applied);
            }
            return false;
        }

        // CANDIDATE LOGIC
        if (in_array('nkrp_candidate', (array) $user->roles)) {
            if ($viewer_id === $candidate_user_id) return true; // Can view their own profile
            
            $sub = $this->getUserSubscription($viewer_id, 'candidate');
            return $sub->plan_key !== 'free'; // Premium Candidates can see other candidates
        }

        return false;
    }

    /**
     * Checks if the user is allowed to use Premium Search Filters (Salary, Featured, Remote)
     */
    public function canUsePremiumFilters(int $viewer_id): bool
    {
        if ($viewer_id === 0) return false;
        if (user_can($viewer_id, 'manage_options')) return true;

        $user = get_userdata($viewer_id);
        if (!$user) return false;

        $role = in_array('nkrp_employer', (array) $user->roles) ? 'employer' : 'candidate';
        $sub = $this->getUserSubscription($viewer_id, $role);
        
        return $sub->plan_key !== 'free';
    }
}