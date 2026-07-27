<?php if (!defined('ABSPATH')) exit; 
$user_id = get_current_user_id();
$is_premium = apply_filters('nkrp_is_user_premium', false, $user_id); 

global $wpdb;
$table = $wpdb->prefix . 'nkrp_messages';

// 1. Fetch all unique conversations for this user
$conversations = $wpdb->get_results($wpdb->prepare("
    SELECT 
        IF(sender_id = %d, receiver_id, sender_id) as other_user_id, 
        MAX(created_at) as last_msg_time,
        SUM(IF(receiver_id = %d AND is_read = 0, 1, 0)) as unread_count
    FROM {$table}
    WHERE sender_id = %d OR receiver_id = %d
    GROUP BY other_user_id
    ORDER BY last_msg_time DESC
", $user_id, $user_id, $user_id, $user_id));

// 2. Determine which chat is actively open
$active_chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : (!empty($conversations) ? (int)$conversations[0]->other_user_id : 0);

// 3. Pre-load active messages if a chat is selected
$active_messages = [];
$active_can_chat = false;
$active_other_user = null;

if ($active_chat_id > 0) {
    $active_other_user = get_userdata($active_chat_id);
    $other_is_premium = apply_filters('nkrp_is_user_premium', false, $active_chat_id);
    $active_can_chat = $is_premium || $other_is_premium;
    
    if ($active_can_chat) {
        // Mark as read
        $wpdb->update($table, ['is_read' => 1], ['receiver_id' => $user_id, 'sender_id' => $active_chat_id], ['%d'], ['%d', '%d']);
        
        $active_messages = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table} 
            WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)
            ORDER BY created_at ASC
        ", $user_id, $active_chat_id, $active_chat_id, $user_id));
    }
}
?>

<div class="nkrp-dashboard-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Messages & Networking</h2>
</div>

<div class="nkrp-messages-container">
    
    <!-- Sidebar: Conversation List -->
    <div class="nkrp-messages-sidebar">
        <div class="nkrp-message-search">
            <span class="dashicons dashicons-search" style="position:absolute; margin: 12px; color: #94a3b8;"></span>
            <input type="text" placeholder="Search employers..." disabled>
        </div>
        
        <div class="nkrp-sidebar-scroll">
            <?php if (empty($conversations)): ?>
                <div style="padding:20px; text-align:center; color:#94a3b8; font-size:13px;">No messages yet.</div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): 
                    $other_user = get_userdata($conv->other_user_id);
                    if (!$other_user) continue;
                    
                    $other_premium = apply_filters('nkrp_is_user_premium', false, $conv->other_user_id);
                    $can_chat = $is_premium || $other_premium;
                    $is_active = $conv->other_user_id == $active_chat_id;
                ?>
                    <div class="nkrp-msg-thread <?= $is_active ? 'active' : '' ?> <?= !$can_chat ? 'nkrp-locked-thread' : '' ?>" data-id="<?= esc_attr((string)$conv->other_user_id) ?>">
                        <div class="nkrp-msg-avatar" style="background:#e0f2fe; color:#0284c7;">
                            <span class="dashicons dashicons-building"></span>
                            <?php if ($can_chat): ?><span class="nkrp-status-dot online"></span><?php endif; ?>
                        </div>
                        <div class="nkrp-msg-preview">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <strong><?= esc_html($other_user->display_name) ?></strong>
                                <div class="nkrp-msg-time"><?= date_i18n('M j', strtotime($conv->last_msg_time)) ?></div>
                            </div>
                            <p><?= $can_chat ? 'Click to view conversation' : 'Premium message locked' ?></p>
                        </div>
                        <?php if ($conv->unread_count > 0 && $can_chat): ?>
                            <div class="nkrp-unread-badge"><?= (int)$conv->unread_count ?></div>
                        <?php endif; ?>
                        
                        <?php if (!$can_chat): ?>
                            <div class="nkrp-lock-icon" style="position:absolute; right:15px; color:#fbbf24;"><span class="dashicons dashicons-lock"></span></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content: Active Chat -->
    <div class="nkrp-messages-main">
        <?php if ($active_chat_id === 0): ?>
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#94a3b8;">
                <span class="dashicons dashicons-format-chat" style="font-size:48px; width:48px; height:48px; margin-bottom:15px;"></span>
                <p>Select a conversation to view messages</p>
            </div>
        <?php elseif (!$active_can_chat): ?>
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding:40px; text-align:center; background:#f8fafc;">
                <div class="nkrp-icon-glow"><span class="dashicons dashicons-lock"></span></div>
                <h3 style="margin:0 0 10px 0;">Premium Conversation</h3>
                <p style="color:#64748b; max-width:400px;">Neither you nor this employer has an active Premium plan. Upgrade your account to network directly with recruiters.</p>
                <a href="<?= esc_url(home_url('/membership/')) ?>" class="nkrp-btn-primary" style="text-decoration:none; margin-top:20px;">Upgrade Now</a>
            </div>
        <?php else: ?>
            <div class="nkrp-chat-header">
                <div style="display:flex; align-items:center; gap:15px;">
                    <div class="nkrp-msg-avatar" style="background:#e0f2fe; color:#0284c7; width:45px; height:45px;">
                        <span class="dashicons dashicons-building" style="margin-top:12px;"></span>
                    </div>
                    <div>
                        <h4 style="margin:0 0 4px 0; font-size:16px; color:#0f172a;" id="nkrp-chat-title"><?= esc_html($active_other_user->display_name) ?></h4>
                    </div>
                </div>
            </div>

            <div class="nkrp-chat-body" id="nkrp-chat-window">
                <?php foreach ($active_messages as $msg): 
                    $is_mine = (int)$msg->sender_id === $user_id;
                ?>
                    <div class="nkrp-chat-bubble <?= $is_mine ? 'sent' : 'received' ?>">
                        <?php if (!$is_mine): ?>
                            <div class="nkrp-bubble-avatar" style="background:#e0f2fe; color:#0284c7;"><span class="dashicons dashicons-building"></span></div>
                            <div class="nkrp-bubble-content">
                                <p><?= wp_kses_post($msg->message) ?></p>
                                <span class="nkrp-chat-timestamp"><?= date('g:i A', strtotime($msg->created_at)) ?></span>
                            </div>
                        <?php else: ?>
                            <p><?= wp_kses_post($msg->message) ?></p>
                            <span class="nkrp-chat-timestamp"><?= date('g:i A', strtotime($msg->created_at)) ?> <span class="dashicons dashicons-saved" style="font-size:12px; width:12px; height:12px;"></span></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="nkrp-chat-input-area">
                <textarea id="nkrp-message-input" placeholder="Type your reply..."></textarea>
                <button id="nkrp-send-btn" class="nkrp-btn-send"><span class="dashicons dashicons-controls-play"></span></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const ajaxUrl = '<?= admin_url('admin-ajax.php') ?>';
    const securityNonce = '<?= wp_create_nonce('nkrp_chat_nonce') ?>';
    let activeChatUserId = <?= $active_chat_id ?>;

    let chatWindow = $('#nkrp-chat-window');
    if(chatWindow.length) {
        chatWindow.scrollTop(chatWindow[0].scrollHeight);
    }

    $('.nkrp-msg-thread').on('click', function() {
        let threadId = $(this).data('id');
        let currentUrl = window.location.href.split('?')[0] + '?tab=messages&chat_id=' + threadId;
        window.location.href = currentUrl; 
    });

    $('#nkrp-send-btn').on('click', function(e) {
        e.preventDefault();
        let msgInput = $('#nkrp-message-input');
        let messageText = msgInput.val().trim();
        
        if(messageText === '' || activeChatUserId === 0) return;
        
        let tempHtml = `
            <div class="nkrp-chat-bubble sent" style="opacity:0.6;">
                <p>${messageText}</p>
                <span class="nkrp-chat-timestamp">Sending...</span>
            </div>`;
        chatWindow.append(tempHtml);
        msgInput.val('');
        chatWindow.scrollTop(chatWindow[0].scrollHeight);

        $.post(ajaxUrl, {
            action: 'nkrp_send_message',
            security: securityNonce,
            receiver_id: activeChatUserId,
            message: messageText
        }, function(response) {
            if(response.success) {
                $('.nkrp-chat-bubble.sent').last().css('opacity', '1')
                    .find('.nkrp-chat-timestamp').html(`${response.data.timestamp} <span class="dashicons dashicons-saved" style="font-size:12px; width:12px; height:12px;"></span>`);
            } else {
                alert("Failed to send message.");
                $('.nkrp-chat-bubble.sent').last().remove();
            }
        });
    });
    
    $('#nkrp-message-input').on('keypress', function(e) {
        if(e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            $('#nkrp-send-btn').click();
        }
    });
});
</script>

<style>
    /* Exact same CSS styling as Employer Inbox for consistency */
    .nkrp-messages-container { display: grid; grid-template-columns: 350px 1fr; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; height: 650px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    .nkrp-messages-sidebar { border-right: 1px solid #e2e8f0; background: #f8fafc; display: flex; flex-direction: column; position: relative; }
    .nkrp-sidebar-scroll { overflow-y: auto; flex: 1; }
    .nkrp-message-search { padding: 15px; border-bottom: 1px solid #e2e8f0; position: relative; }
    .nkrp-message-search input { width: 100%; padding: 10px 15px 10px 35px; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 13px; box-sizing: border-box; background: #fff; }
    
    .nkrp-msg-thread { display: flex; gap: 15px; padding: 15px; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; position: relative; }
    .nkrp-msg-thread:hover { background: #f1f5f9; }
    .nkrp-msg-thread.active { background: #fff; border-left: 4px solid #2563eb; }
    .nkrp-locked-thread { filter: grayscale(1); opacity: 0.6; }
    .nkrp-locked-thread .nkrp-msg-preview p { filter: blur(3px); user-select: none; }
    
    .nkrp-msg-avatar { position: relative; width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nkrp-status-dot { position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; }
    .nkrp-status-dot.online { background: #16a34a; }
    
    .nkrp-msg-preview { flex: 1; overflow: hidden; }
    .nkrp-msg-preview p { margin: 0; font-size: 13px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nkrp-msg-time { font-size: 11px; color: #94a3b8; }
    .nkrp-unread-badge { background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%; position: absolute; right: 15px; bottom: 15px; }

    .nkrp-icon-glow { width: 50px; height: 50px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; box-shadow: 0 4px 6px -1px rgba(251, 191, 36, 0.3); }
    .nkrp-icon-glow .dashicons { font-size: 24px; color: #d97706; margin-top: 5px; }

    .nkrp-messages-main { display: flex; flex-direction: column; background: #f8fafc; }
    .nkrp-chat-header { padding: 15px 25px; border-bottom: 1px solid #e2e8f0; background: #fff; display: flex; justify-content: space-between; align-items: center; }

    .nkrp-chat-body { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; }
    .nkrp-chat-bubble { max-width: 70%; display: flex; flex-direction: column; position: relative; }
    .nkrp-chat-bubble p { margin: 0; padding: 14px 18px; border-radius: 18px; font-size: 14px; line-height: 1.5; }
    .nkrp-chat-timestamp { font-size: 11px; color: #94a3b8; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
    
    .nkrp-chat-bubble.sent { align-self: flex-end; }
    .nkrp-chat-bubble.sent p { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
    .nkrp-chat-bubble.sent .nkrp-chat-timestamp { align-self: flex-end; }
    
    .nkrp-chat-bubble.received { align-self: flex-start; display: flex; flex-direction: row; gap: 10px; align-items: flex-end; }
    .nkrp-bubble-avatar { width: 28px; height: 28px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #94a3b8; margin-bottom: 20px; }
    .nkrp-bubble-avatar .dashicons { font-size: 16px; width: 16px; height: 16px; }
    .nkrp-chat-bubble.received p { background: #fff; color: #334155; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }

    .nkrp-chat-input-area { padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #fff; display: flex; gap: 12px; align-items: flex-end; }
    .nkrp-chat-input-area textarea { flex: 1; border: 1px solid #cbd5e1; border-radius: 20px; padding: 12px 18px; font-size: 14px; resize: none; font-family: inherit; max-height: 100px; outline: none; transition: border-color 0.2s; }
    .nkrp-chat-input-area textarea:focus { border-color: #2563eb; }
    .nkrp-btn-send { background: #2563eb; color: #fff; border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
    .nkrp-btn-send:hover { background: #1d4ed8; }
    .nkrp-btn-send .dashicons { transform: rotate(90deg); margin-left: 2px; }
    
    @media(max-width: 768px) {
        .nkrp-messages-container { grid-template-columns: 1fr; }
        .nkrp-messages-sidebar { display: none; }
    }
</style>