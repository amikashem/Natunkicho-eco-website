<?php

declare(strict_types=1);

namespace NKRecruitment\Messages;

if (!defined('ABSPATH')) {
    exit;
}

class MessageController
{
    public function register(): void
    {
        // Ensure the database table exists automatically
        add_action('init', [$this, 'initializeDatabase']);

        // AJAX endpoints for real-time chatting
        add_action('wp_ajax_nkrp_send_msg', [$this, 'sendMessage']);
        add_action('wp_ajax_nkrp_get_msgs', [$this, 'fetchMessages']);
    }

    public function initializeDatabase(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_messages';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sender_id BIGINT UNSIGNED NOT NULL,
                receiver_id BIGINT UNSIGNED NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY sender_id (sender_id),
                KEY receiver_id (receiver_id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function sendMessage(): void
    {
        check_ajax_referer('nk_msg_nonce', 'security');
        
        $sender_id = get_current_user_id();
        $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if ($sender_id === 0 || $receiver_id === 0 || empty($message)) {
            wp_send_json_error('Invalid data.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_messages';
        
        $wpdb->insert($table, [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'created_at' => current_time('mysql')
        ]);

        wp_send_json_success(['id' => $wpdb->insert_id]);
    }

    public function fetchMessages(): void
    {
        check_ajax_referer('nk_msg_nonce', 'security');
        
        $user_id = get_current_user_id();
        $contact_id = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : 0;
        $last_id = isset($_POST['last_id']) ? (int)$_POST['last_id'] : 0;

        if ($user_id === 0 || $contact_id === 0) {
            wp_send_json_error();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_messages';

        // Mark incoming messages as read
        $wpdb->update($table, ['is_read' => 1], ['sender_id' => $contact_id, 'receiver_id' => $user_id]);

        $messages = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table} 
            WHERE ((sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d))
            AND id > %d
            ORDER BY created_at ASC
        ", $user_id, $contact_id, $contact_id, $user_id, $last_id));

        $html = '';
        $new_last_id = $last_id;

        foreach ($messages as $msg) {
            $is_sent = ((int)$msg->sender_id === $user_id);
            $class = $is_sent ? 'sent' : 'received';
            $time = date('h:i A', strtotime($msg->created_at));
            
            $status_icon = ($is_sent && $msg->is_read) ? '<span class="dashicons dashicons-saved" style="color:#22c55e; font-size:12px; width:12px; height:12px;"></span>' : '';

            $html .= '<div class="nk-bubble-row ' . $class . '">';
            $html .= '<div class="nk-bubble">' . wp_kses_post(nl2br(esc_html($msg->message))) . '</div>';
            $html .= '<span class="time">' . $time . ' ' . $status_icon . '</span>';
            $html .= '</div>';
            
            $new_last_id = $msg->id;
        }

        wp_send_json_success(['html' => $html, 'last_id' => $new_last_id]);
    }
}