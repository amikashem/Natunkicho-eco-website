<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$user_id = get_current_user_id();
$msg_table = $wpdb->prefix . 'nkrp_messages';

// =====================================================================
// 🔥 10X FIX: AUTO-CREATE TABLE IF MISSING (Prevents Blank Screen Errors)
// =====================================================================
if ($wpdb->get_var("SHOW TABLES LIKE '{$msg_table}'") != $msg_table) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$msg_table} (
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

// 1. Get the requested chat ID from URL
$active_chat_id = isset($_GET['new_msg']) ? (int)$_GET['new_msg'] : (isset($_GET['chat']) ? (int)$_GET['chat'] : null);

// 2. Fetch all unique contacts this user has messaged (Bulletproofed)
$contacts_query = $wpdb->get_results($wpdb->prepare("
    SELECT DISTINCT IF(sender_id = %d, receiver_id, sender_id) as contact_id 
    FROM {$msg_table} 
    WHERE sender_id = %d OR receiver_id = %d
", $user_id, $user_id, $user_id));

$contact_ids = [];
if (!empty($contacts_query) && !is_wp_error($contacts_query)) {
    foreach ($contacts_query as $c) {
        $contact_ids[] = (int)$c->contact_id;
    }
}

// If we clicked a new candidate, add them to the list even if no messages exist yet
if ($active_chat_id && !in_array($active_chat_id, $contact_ids)) {
    array_unshift($contact_ids, $active_chat_id); 
}

$msg_nonce = wp_create_nonce('nk_msg_nonce');
?>

<div class="nkrp-dashboard-header">
    <h2>Direct Messages</h2>
    <p style="color: #64748b; margin: 5px 0 0 0;">Connect with talent and employers in real-time.</p>
</div>

<div class="nk-msg-app">
    
    <div class="nk-msg-sidebar">
        <div class="nk-msg-search">
            <span class="dashicons dashicons-search"></span>
            <input type="text" placeholder="Search messages...">
        </div>
        
        <div class="nk-msg-list">
            <?php if (empty($contact_ids)): ?>
                <div style="padding: 20px; text-align: center; color: #94a3b8; font-size: 13px;">No conversations yet.</div>
            <?php else: ?>
                <?php foreach ($contact_ids as $cid): 
                    $u = get_userdata($cid);
                    if (!$u) continue;
                    
                    $name = trim($u->first_name . ' ' . $u->last_name) ?: $u->display_name;
                    $initial = strtoupper(substr($name, 0, 1));
                    $title = get_user_meta($cid, '_nkrp_professional_title', true) ?: 'User';
                    
                    // Get latest message for preview safely
                    $last_msg = $wpdb->get_row($wpdb->prepare("
                        SELECT * FROM {$msg_table} 
                        WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d) 
                        ORDER BY created_at DESC LIMIT 1
                    ", $user_id, $cid, $cid, $user_id));

                    $preview_text = $last_msg ? wp_trim_words($last_msg->message, 5) : 'Say hello...';
                    $time_display = $last_msg ? human_time_diff(strtotime($last_msg->created_at), current_time('timestamp')) : 'New';
                    $is_unread = ($last_msg && (int)$last_msg->receiver_id === $user_id && (int)$last_msg->is_read === 0);
                    
                    // Determine which URL base to use depending on active dashboard
                    $base_dash = strpos($_SERVER['REQUEST_URI'], 'candidate') !== false ? '/candidate-dashboard/' : '/employer-dashboard/';
                ?>
                    <a href="<?= esc_url(add_query_arg(['tab' => 'messages', 'chat' => $cid], home_url($base_dash))) ?>" 
                       class="nk-msg-item <?= ($active_chat_id === $cid) ? 'active' : '' ?> <?= $is_unread ? 'unread' : '' ?>" style="text-decoration:none;">
                        <div class="nk-avatar-wrap">
                            <div class="nk-avatar"><?= esc_html($initial) ?></div>
                            <span class="nk-online-dot"></span>
                        </div>
                        <div class="nk-msg-preview">
                            <div class="nk-msg-meta">
                                <h4><?= esc_html($name) ?></h4>
                                <span class="time"><?= esc_html($time_display) ?></span>
                            </div>
                            <span class="role"><?= esc_html($title) ?></span>
                            <p><?= esc_html($preview_text) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="nk-msg-window">
        <?php if ($active_chat_id): 
            $active_user = get_userdata($active_chat_id);
            if($active_user) {
                $active_name = trim($active_user->first_name . ' ' . $active_user->last_name) ?: $active_user->display_name;
                $active_initial = strtoupper(substr($active_name, 0, 1));
            } else {
                $active_name = "Unknown User";
                $active_initial = "?";
            }
            
            // Fetch history safely
            $wpdb->update($msg_table, ['is_read' => 1], ['sender_id' => $active_chat_id, 'receiver_id' => $user_id]);
            $history = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM {$msg_table} 
                WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d) 
                ORDER BY created_at ASC
            ", $user_id, $active_chat_id, $active_chat_id, $user_id)) ?: [];
            
            $last_msg_id = 0;
        ?>
            <div class="nk-msg-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="nk-avatar-wrap">
                        <div class="nk-avatar"><?= esc_html($active_initial) ?></div>
                        <span class="nk-online-dot"></span>
                    </div>
                    <div class="nk-chat-info">
                        <h4><?= esc_html($active_name) ?></h4>
                        <span class="status">Online</span>
                    </div>
                </div>
                <div class="nk-chat-actions">
                    <a href="<?= esc_url(home_url('/candidate-profile/?id=' . $active_chat_id)) ?>" target="_blank" class="nkrp-btn-secondary" style="padding: 6px 12px; font-size: 13px;">View Profile</a>
                </div>
            </div>

            <div class="nk-msg-history" id="nk-chat-box">
                <?php foreach ($history as $msg): 
                    $last_msg_id = $msg->id;
                    $is_sent = ((int)$msg->sender_id === $user_id);
                    $status_icon = ($is_sent && $msg->is_read) ? '<span class="dashicons dashicons-saved" style="color:#22c55e; font-size:12px; width:12px; height:12px;"></span>' : '';
                ?>
                    <div class="nk-bubble-row <?= $is_sent ? 'sent' : 'received' ?>">
                        <div class="nk-bubble"><?= wp_kses_post(nl2br(esc_html($msg->message))) ?></div>
                        <span class="time"><?= date('h:i A', strtotime($msg->created_at)) ?> <?= $status_icon ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="nk-msg-composer">
                <textarea id="nk-msg-input" placeholder="Type a message..." rows="1"></textarea>
                <button id="nk-send-btn" class="nk-send-btn"><span class="dashicons dashicons-controls-play" style="transform: rotate(90deg);"></span></button>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const chatBox = document.getElementById("nk-chat-box");
                const input = document.getElementById("nk-msg-input");
                const sendBtn = document.getElementById("nk-send-btn");
                const ajaxUrl = "<?= admin_url('admin-ajax.php') ?>";
                let lastMsgId = <?= (int)$last_msg_id ?>;
                const contactId = <?= (int)$active_chat_id ?>;
                const nonce = "<?= $msg_nonce ?>";

                function scrollToBottom() {
                    if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                }
                scrollToBottom();

                function sendMessage() {
                    const text = input.value.trim();
                    if (!text) return;
                    input.value = "";
                    input.disabled = true;

                    const formData = new URLSearchParams();
                    formData.append("action", "nkrp_send_msg");
                    formData.append("security", nonce);
                    formData.append("receiver_id", contactId);
                    formData.append("message", text);

                    fetch(ajaxUrl, { method: "POST", body: formData })
                        .then(r => r.json())
                        .then(res => {
                            input.disabled = false;
                            input.focus();
                            fetchNewMessages(); 
                        }).catch(e => {
                            input.disabled = false;
                        });
                }

                function fetchNewMessages() {
                    const formData = new URLSearchParams();
                    formData.append("action", "nkrp_get_msgs");
                    formData.append("security", nonce);
                    formData.append("contact_id", contactId);
                    formData.append("last_id", lastMsgId);

                    fetch(ajaxUrl, { method: "POST", body: formData })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success && res.data && res.data.html !== "") {
                                chatBox.insertAdjacentHTML("beforeend", res.data.html);
                                lastMsgId = res.data.last_id;
                                scrollToBottom();
                            }
                        }).catch(e => {});
                }

                if(sendBtn && input) {
                    sendBtn.addEventListener("click", sendMessage);
                    input.addEventListener("keypress", function(e) {
                        if (e.key === "Enter" && !e.shiftKey) {
                            e.preventDefault();
                            sendMessage();
                        }
                    });
                    // Poll for new messages every 3 seconds
                    setInterval(fetchNewMessages, 3000);
                }
            });
            </script>

        <?php else: ?>
            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8;">
                <span class="dashicons dashicons-format-chat" style="font-size: 64px; width: 64px; height: 64px; margin-bottom: 20px; color: #e2e8f0;"></span>
                <h3 style="margin: 0 0 10px 0; color: #475569;">Your Messages</h3>
                <p style="margin: 0;">Select a conversation to start chatting.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* LinkedIn-Style Messaging App Layout */
    .nk-msg-app { display: flex; height: 650px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .nk-msg-sidebar { width: 320px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; background: #f8fafc; flex-shrink: 0; }
    .nk-msg-search { padding: 15px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; background: #fff; }
    .nk-msg-search .dashicons { color: #94a3b8; font-size: 18px; margin-right: 10px; }
    .nk-msg-search input { width: 100%; border: none; outline: none; font-size: 14px; background: transparent; }
    .nk-msg-list { flex: 1; overflow-y: auto; }
    .nk-msg-item { padding: 15px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; cursor: pointer; transition: background 0.2s; background: #fff; }
    .nk-msg-item:hover { background: #f1f5f9; }
    .nk-msg-item.active { background: #eff6ff; border-left: 3px solid #2563eb; }
    .nk-msg-item.unread { background: #fff; }
    .nk-msg-item.unread h4 { font-weight: 800 !important; color: #0f172a; }
    .nk-avatar-wrap { position: relative; width: 48px; height: 48px; flex-shrink: 0; }
    .nk-avatar { width: 100%; height: 100%; border-radius: 50%; background: #dbeafe; color: #1e40af; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; }
    .nk-online-dot { position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background: #22c55e; border: 2px solid #fff; border-radius: 50%; }
    .nk-msg-preview { flex: 1; min-width: 0; }
    .nk-msg-meta { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 2px; }
    .nk-msg-meta h4 { margin: 0; font-size: 15px; color: #334155; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nk-msg-meta .time { font-size: 11px; color: #94a3b8; white-space: nowrap; }
    .nk-msg-preview .role { display: block; font-size: 12px; color: #2563eb; margin-bottom: 4px; }
    .nk-msg-preview p { margin: 0; font-size: 13px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nk-msg-window { flex: 1; display: flex; flex-direction: column; background: #fff; min-width: 0; }
    .nk-msg-header { padding: 15px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff; }
    .nk-chat-info h4 { margin: 0 0 2px 0; font-size: 16px; color: #0f172a; }
    .nk-chat-info .status { font-size: 12px; color: #22c55e; font-weight: 600; }
    .nk-chat-actions { display: flex; gap: 10px; align-items: center; }
    .nk-icon-btn { background: none; border: none; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 5px; border-radius: 50%; transition: 0.2s; }
    .nk-icon-btn:hover { background: #f1f5f9; color: #0f172a; }
    .nk-msg-history { flex: 1; padding: 25px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 20px; }
    .nk-bubble-row { display: flex; flex-direction: column; max-width: 75%; }
    .nk-bubble-row.received { align-self: flex-start; }
    .nk-bubble-row.sent { align-self: flex-end; align-items: flex-end; }
    .nk-bubble { padding: 12px 16px; border-radius: 12px; font-size: 14px; line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .nk-bubble-row.received .nk-bubble { background: #fff; border: 1px solid #e2e8f0; color: #334155; border-bottom-left-radius: 2px; }
    .nk-bubble-row.sent .nk-bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 2px; }
    .nk-bubble-row .time { font-size: 11px; color: #94a3b8; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
    .nk-msg-composer { padding: 15px 25px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; background: #fff; }
    .nk-msg-composer textarea { flex: 1; border: 1px solid #cbd5e1; border-radius: 24px; padding: 12px 20px; font-size: 14px; outline: none; resize: none; font-family: inherit; background: #f8fafc; transition: border 0.2s; }
    .nk-msg-composer textarea:focus { border-color: #94a3b8; background: #fff; }
    .nk-send-btn { background: #2563eb; color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 4px rgba(37,99,235,0.2); }
    .nk-send-btn:hover { background: #1d4ed8; transform: scale(1.05); }
    @media(max-width: 768px) {
        .nk-msg-app { flex-direction: column; height: 800px; }
        .nk-msg-sidebar { width: 100%; height: 300px; border-right: none; border-bottom: 1px solid #e2e8f0; }
    }
</style>