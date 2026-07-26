<?php

declare(strict_types=1);

namespace NKRecruitment\Membership\Admin;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class MembershipAdmin
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'handleManualUpgrade']);
    }

    public function addAdminMenu(): void
    {
        add_menu_page(
            'Memberships',
            'Memberships',
            'manage_options',
            'nkrp-memberships',
            [$this, 'renderAdminPage'],
            'dashicons-star-filled',
            56
        );
    }

    public function handleManualUpgrade(): void
    {
        if (isset($_GET['page']) && $_GET['page'] === 'nkrp-memberships' && isset($_POST['nkrp_manual_upgrade'])) {
            if (!current_user_can('manage_options')) wp_die('Unauthorized');
            
            // Added security nonce check
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'nkrp_manual_upgrade_nonce')) {
                wp_die('Security check failed.');
            }
            
            $user_id = (int) $_POST['user_id'];
            $new_plan = sanitize_text_field($_POST['plan_key']);
            $duration_days = (int) $_POST['duration_days'];
            
            $user = get_userdata($user_id);
            if (!$user) {
                wp_die('Invalid User ID');
            }

            // Retained your logic to define user_type dynamically
            $role = in_array('nkrp_employer', (array) $user->roles) ? 'employer' : 'candidate';

            global $wpdb;
            $table = DatabaseManager::table('subscriptions');
            
            // Calculate Expiry Date for Phase 1 Requirements
            $started_at = current_time('mysql');
            if ($duration_days === 9999) {
                $expires_at = '0000-00-00 00:00:00'; // Lifetime flag
            } else {
                $expires_at = date('Y-m-d H:i:s', strtotime("+{$duration_days} days", current_time('timestamp')));
            }

            // Ensure any previous active memberships for this user are marked expired
            $wpdb->update($table, ['status' => 'expired'], ['user_id' => $user_id, 'status' => 'active']);
            
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE user_id = %d LIMIT 1", $user_id));
            
            if ($exists) {
                $wpdb->update($table, [
                    'plan_key' => $new_plan, 
                    'status' => 'active',
                    'user_type' => $role,
                    'started_at' => $started_at,
                    'expires_at' => $expires_at
                ], ['user_id' => $user_id]);
            } else {
                $wpdb->insert($table, [
                    'user_id' => $user_id,
                    'user_type' => $role,
                    'plan_key' => $new_plan,
                    'status' => 'active',
                    'started_at' => $started_at,
                    'expires_at' => $expires_at
                ]);
            }
            
            wp_redirect(admin_url('admin.php?page=nkrp-memberships&updated=1'));
            exit;
        }
    }

    public function renderAdminPage(): void
    {
        global $wpdb;
        $table = DatabaseManager::table('subscriptions');
        $subs = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC LIMIT 50");
        
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Platform Memberships</h1>';
        echo '<hr class="wp-header-end">';
        
        if (isset($_GET['updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>User subscription upgraded and active.</p></div>';
        }

        // Split Layout for a cleaner, professional enterprise look
        echo '<div style="display:flex; gap: 20px; margin-top: 20px;">';

        // --- LEFT COLUMN: ASSIGNMENT FORM ---
        echo '<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
        echo '<h2>Assign Membership</h2>';
        echo '<p>Manually assign a plan bypassing WooCommerce.</p>';
        
        echo '<form method="POST">';
        wp_nonce_field('nkrp_manual_upgrade_nonce');
        echo '<table class="form-table"><tbody>';
        
        echo '<tr><th><label>User ID</label></th><td><input type="number" name="user_id" required class="regular-text" placeholder="e.g. 12"></td></tr>';
        
        echo '<tr><th><label>Plan / Product ID</label></th><td><input type="text" name="plan_key" required class="regular-text" placeholder="e.g. 154 (Woo Product ID)"></td></tr>';
        
        echo '<tr><th><label>Duration</label></th><td><select name="duration_days">';
        echo '<option value="90">3 Months (90 Days)</option>';
        echo '<option value="180">6 Months (180 Days)</option>';
        echo '<option value="365">12 Months (365 Days)</option>';
        echo '<option value="9999">Lifetime (Never Expires)</option>';
        echo '</select></td></tr>';
        
        echo '</tbody></table>';
        echo '<p class="submit"><button type="submit" name="nkrp_manual_upgrade" class="button button-primary">Grant Access</button></p>';
        echo '</form>';
        echo '</div>';

        // --- RIGHT COLUMN: ACTIVE USERS TABLE ---
        echo '<div style="flex: 2; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
        echo '<h2>Active & Recent Users</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>User</th><th>Type</th><th>Plan ID</th><th>Status</th><th>Expires On</th></tr></thead>';
        echo '<tbody>';
        
        if (empty($subs)) {
            echo '<tr><td colspan="5">No subscriptions found.</td></tr>';
        } else {
            foreach ($subs as $sub) {
                $user = get_userdata((int) $sub->user_id);
                $email = $user ? $user->user_email : 'Deleted User';
                
                // Determine styling based on status
                $is_active = (isset($sub->status) && $sub->status === 'active');
                $badge = $is_active ? 'background:#dcfce7; color:#166534; border:1px solid #bbf7d0;' : 'background:#fee2e2; color:#991b1b; border:1px solid #fecaca;';
                $status_text = $is_active ? 'Active' : 'Expired';
                
                echo '<tr>';
                echo '<td><strong>#' . esc_html((string)$sub->user_id) . '</strong><br><small>' . esc_html($email) . '</small></td>';
                echo '<td>' . esc_html(ucfirst($sub->user_type ?? 'unknown')) . '</td>';
                echo '<td><strong>' . esc_html(strtoupper($sub->plan_key)) . '</strong></td>';
                echo '<td><span style="padding:4px 10px; border-radius:12px; font-weight:bold; font-size:11px; ' . $badge . '">' . esc_html($status_text) . '</span></td>';
                
                // Show Expiry Date cleanly
                $expires = (isset($sub->expires_at) && $sub->expires_at !== '0000-00-00 00:00:00') ? date('M j, Y', strtotime($sub->expires_at)) : 'Lifetime';
                echo '<td>' . esc_html($expires) . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table></div>';
        
        echo '</div></div>';
    }
}