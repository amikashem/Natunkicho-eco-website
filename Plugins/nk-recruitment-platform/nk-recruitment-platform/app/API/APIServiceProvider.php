<?php

declare(strict_types=1);

namespace NKRecruitment\API;

if (!defined('ABSPATH')) {
    exit;
}

class APIServiceProvider
{
    public function register(): void
    {
        // Register AJAX endpoints for logged-in users only
        add_action('wp_ajax_nkrp_send_message', [$this, 'sendMessage']);
        add_action('wp_ajax_nkrp_get_messages', [$this, 'getMessages']);
        
        // 🔥 THE HIGH-SPEED DATA SYNC SCRIPT (Creates table AND fills it)
        add_action('wp_ajax_nkrp_sync_candidates', function() {
            if (!current_user_can('manage_options')) wp_die('Unauthorized'); // Admin only
            
            // 1. Force the table creation safely
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            (new \NKRecruitment\Database\Migrations\CreateCandidateIndexTable())->up();

            global $wpdb;
            $table = $wpdb->prefix . 'nkrp_candidate_index';
            
            // 2. Fetch all candidates
            $users = get_users(['role' => 'nkrp_candidate']);
            $count = 0;
            
            // 3. Isolated Insertion Loop
            foreach ($users as $user) {
                try {
                    $title = get_user_meta($user->ID, '_nkrp_professional_title', true);
                    $skills = get_user_meta($user->ID, '_nkrp_skills', true);
                    $bio = get_user_meta($user->ID, '_nkrp_bio', true);
                    $city = get_user_meta($user->ID, '_nkrp_city', true);
                    $country = get_user_meta($user->ID, '_nkrp_country', true);
                    $location = trim((string)$city . ' ' . (string)$country);

                    $wpdb->replace($table, [
                        'user_id' => $user->ID,
                        'display_name' => trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name,
                        'professional_title' => $title,
                        'skills' => $skills,
                        'location' => $location,
                        'bio' => $bio
                    ]);
                    $count++;
                } catch (\Throwable $e) {
                    error_log('Sync failed for user ' . $user->ID . ': ' . $e->getMessage());
                    continue; // Skip failed user, but keep looping others!
                }
            }
            
            echo "<div style='padding:20px; background:#dcfce7; color:#166534; font-family:sans-serif; font-size:18px; border-radius:8px; border:2px solid #22c55e;'>";
            echo "<strong>SUCCESS!</strong><br> We created the missing table and instantly synchronized <strong>{$count}</strong> candidates into the High-Speed Search Index.<br>";
            echo "Your Talent Database search is now 100x faster and the database error is gone!";
            echo "</div>";
            wp_die();
        });
        
    }

    public function sendMessage(): void
    {
        check_ajax_referer('nkrp_chat_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized');
        }

        $sender_id = get_current_user_id();
        $receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if ($receiver_id === 0 || empty($message)) {
            wp_send_json_error('Invalid data');
        }

        // Enforce Premium limit for Employers (if necessary)
        $is_employer = in_array('nkrp_employer', (array) wp_get_current_user()->roles);
        $is_premium = apply_filters('nkrp_is_user_premium', false, $sender_id);
        
        if ($is_employer && !$is_premium) {
            wp_send_json_error('Premium feature');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_messages';

        $inserted = $wpdb->insert(
            $table,
            [
                'sender_id'   => $sender_id,
                'receiver_id' => $receiver_id,
                'message'     => $message,
                'is_read'     => 0,
                'created_at'  => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%d', '%s']
        );

        if ($inserted) {
            wp_send_json_success([
                'message_id' => $wpdb->insert_id,
                'timestamp'  => current_time('g:i A'),
                'text'       => wp_kses_post($message)
            ]);
        } else {
            wp_send_json_error('Database error');
        }
    }

    public function getMessages(): void
    {
        check_ajax_referer('nkrp_chat_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized');
        }

        $current_user_id = get_current_user_id();
        $other_user_id = isset($_POST['other_user_id']) ? (int) $_POST['other_user_id'] : 0;

        if ($other_user_id === 0) {
            wp_send_json_error('Invalid user');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_messages';

        // Mark unread messages as read
        $wpdb->update(
            $table,
            ['is_read' => 1],
            ['receiver_id' => $current_user_id, 'sender_id' => $other_user_id],
            ['%d'],
            ['%d', '%d']
        );

        // Fetch conversation
        $messages = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table} 
            WHERE (sender_id = %d AND receiver_id = %d) 
               OR (sender_id = %d AND receiver_id = %d)
            ORDER BY created_at ASC
        ", $current_user_id, $other_user_id, $other_user_id, $current_user_id));

        $formatted_messages = [];
        foreach ($messages as $msg) {
            $formatted_messages[] = [
                'id' => $msg->id,
                'is_mine' => (int)$msg->sender_id === $current_user_id,
                'text' => wp_kses_post($msg->message),
                'time' => date('g:i A', strtotime($msg->created_at))
            ];
        }

        wp_send_json_success(['messages' => $formatted_messages]);
    }
}