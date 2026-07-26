<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function() {
    register_post_type('nk_message', [ 'public' => false, 'show_ui' => true, 'label' => 'Direct Messages', 'supports' => ['editor', 'author'] ]);
});

// 1. AJAX Handler: Send a Message + Email Alert
add_action('wp_ajax_nk_send_message', function() {
    check_ajax_referer('nk_message_nonce', 'security');
    $sender_id = get_current_user_id();
    $receiver_id = intval($_POST['receiver_id']);
    $message = sanitize_textarea_field($_POST['message']);

    if (empty($message) || !$receiver_id) wp_send_json_error('Message cannot be empty.');

    $active_view = function_exists('nk_get_active_workspace') ? nk_get_active_workspace($sender_id) : 'candidate';
    if ( $active_view === 'employer' && !nk_is_user_premium($sender_id) ) wp_send_json_error('Premium required to send messages.');

    $thread_id = ($sender_id < $receiver_id) ? $sender_id . '_' . $receiver_id : $receiver_id . '_' . $sender_id;

    wp_insert_post([
        'post_type' => 'nk_message', 'post_content' => $message, 'post_author' => $sender_id, 'post_status' => 'publish',
        'meta_input' => [ '_receiver_id' => $receiver_id, '_thread_id' => $thread_id, '_is_read' => 0 ]
    ]);

    $sender_info = get_userdata($sender_id);
    $receiver_info = get_userdata($receiver_id);

    // Red Dot In-App Notification
    if (function_exists('nk_add_in_app_notification')) {
        nk_add_in_app_notification($receiver_id, 'New Message 💬', $sender_info->display_name . ' sent you a direct message.', site_url('/dashboard/?tab=messages&chat=' . $sender_id));
    }

    // 🔴 NEW: Send Email Notification Alert!
    if ($receiver_info && function_exists('nk_get_branded_email_html')) {
        $subject = 'New Direct Message from ' . $sender_info->display_name;
        $content = "<p>Hello <strong>" . esc_html($receiver_info->display_name) . "</strong>,</p>";
        $content .= "<p>You have received a new direct message on NatunKicho from <strong>" . esc_html($sender_info->display_name) . "</strong>:</p>";
        $content .= "<div style='background:#f8fafc; padding:15px; border-left:4px solid #0A66C2; margin:15px 0;'><em>\"" . esc_html(wp_trim_words($message, 30, '...')) . "\"</em></div>";
        $content .= "<p>Log in to your Dashboard to read the full message and reply securely.</p>";
        $content .= '<a href="' . esc_url(site_url('/dashboard/?tab=messages&chat=' . $sender_id)) . '" style="display:inline-block; background:#0A66C2; color:#fff; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold; margin-top:15px;">Reply to Message</a>';

        $final_html = nk_get_branded_email_html( $subject, $content );
        add_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
        wp_mail( $receiver_info->user_email, $subject, $final_html );
        remove_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
    }

    wp_send_json_success();
});

// 2. AJAX Handler: Delete Conversation
add_action('wp_ajax_nk_delete_conversation', function() {
    check_ajax_referer('nk_message_nonce', 'security');
    $user_id = get_current_user_id();
    $partner_id = intval($_POST['partner_id']);
    $thread_id = ($user_id < $partner_id) ? $user_id . '_' . $partner_id : $partner_id . '_' . $user_id;

    $msgs = get_posts(['post_type'=>'nk_message', 'meta_key'=>'_thread_id', 'meta_value'=>$thread_id, 'posts_per_page'=>-1]);
    foreach($msgs as $m) { update_post_meta($m->ID, '_deleted_by_' . $user_id, '1'); }
    wp_send_json_success();
});

function nk_get_unread_message_count($user_id) {
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} m1 ON p.ID = m1.post_id
        INNER JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id
        WHERE p.post_type = 'nk_message' AND m1.meta_key = '_receiver_id' AND m1.meta_value = %d AND m2.meta_key = '_is_read' AND m2.meta_value = 0
        AND NOT EXISTS (SELECT 1 FROM {$wpdb->postmeta} m3 WHERE m3.post_id = p.ID AND m3.meta_key = '_deleted_by_%d')
    ", $user_id, $user_id));
    return intval($count);
}

// 3. Render the Inbox UI
function nk_render_messages_page($active_view) {
    $user_id = get_current_user_id();
    $is_premium = function_exists('nk_is_user_premium') ? nk_is_user_premium($user_id) : false;
    $active_chat = isset($_GET['chat']) ? intval($_GET['chat']) : 0;

    global $wpdb;
    $query = $wpdb->prepare("
        SELECT p.post_author, m.meta_value as receiver_id, p.ID as msg_id
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id
        WHERE p.post_type = 'nk_message' AND m.meta_key = '_receiver_id'
        AND (p.post_author = %d OR m.meta_value = %d)
        AND NOT EXISTS (SELECT 1 FROM {$wpdb->postmeta} m2 WHERE m2.post_id = p.ID AND m2.meta_key = '_deleted_by_%d')
        ORDER BY p.post_date DESC
    ", $user_id, $user_id, $user_id);

    $results = $wpdb->get_results($query);
    $partners = [];
    $unread_senders = [];

    foreach ($results as $row) {
        $partner_id = ($row->post_author == $user_id) ? $row->receiver_id : $row->post_author;
        if (!in_array($partner_id, $partners)) $partners[] = $partner_id;
        if ($row->receiver_id == $user_id && get_post_meta($row->msg_id, '_is_read', true) == 0) $unread_senders[] = $partner_id;
    }

    if ($active_chat && !in_array($active_chat, $partners)) array_unshift($partners, $active_chat);

    ob_start();
    ?>
    <div class="nk-messages-wrapper" style="display: flex; gap: 0; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; height: 600px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        
        <div style="width: 300px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; background: #f8fafc;">
            <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; background: #fff;">
                <h3 style="margin: 0; font-size: 16px; color: #0f172a;">Conversations</h3>
            </div>
            <div style="overflow-y: auto; flex: 1; padding: 10px;">
                <?php if (empty($partners)) : ?>
                    <p style="padding: 20px; text-align: center; color: #64748b; font-size: 13px;">No messages yet.</p>
                <?php else : ?>
                    <?php foreach ($partners as $pid) : 
                        $partner_info = get_userdata($pid);
                        if (!$partner_info) continue;
                        $is_active = ($active_chat == $pid);
                        $bg = $is_active ? '#e0f2fe' : 'transparent';
                        $is_unread = in_array($pid, $unread_senders);
                        $weight = $is_unread ? '800' : '500';
                    ?>
                        <a href="?tab=messages&chat=<?php echo $pid; ?>" style="display: flex; align-items: center; gap: 10px; padding: 15px; border-radius: 8px; text-decoration: none; color: #334155; background: <?php echo $bg; ?>; transition: background 0.2s; margin-bottom: 5px;">
                            <div style="width: 40px; height: 40px; background: #cbd5e1; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #fff; font-weight: bold; font-size: 16px; position: relative;">
                                <?php echo strtoupper(substr($partner_info->display_name, 0, 1)); ?>
                                <?php if($is_unread): ?><span style="position:absolute; top:-2px; right:-2px; width:12px; height:12px; background:#ef4444; border-radius:50%; border:2px solid #fff;"></span><?php endif; ?>
                            </div>
                            <div style="flex: 1; overflow: hidden;">
                                <strong style="display: block; font-size: 14px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: <?php echo $weight; ?>;"><?php echo esc_html($partner_info->display_name); ?></strong>
                                <span style="font-size: 12px; color: #64748b; font-weight: <?php echo $weight; ?>;"><?php echo $is_unread ? 'New Message!' : 'View thread &rarr;'; ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="flex: 1; display: flex; flex-direction: column; position: relative;">
            <?php if (!$active_chat) : ?>
                <div style="flex: 1; display: flex; justify-content: center; align-items: center; flex-direction: column; color: #94a3b8; padding: 40px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 15px;">💬</div>
                    <h3 style="margin: 0 0 10px 0; color: #0f172a;">Your Inbox</h3>
                    <?php if($active_view === 'employer'): ?>
                        <p style="margin: 0; font-size: 14px; line-height: 1.5; max-width: 350px;">To start a new conversation, visit the <strong>Applicant Tracker</strong> and click "Message" next to a candidate's name.</p>
                    <?php else: ?>
                        <p style="margin: 0; font-size: 14px; line-height: 1.5; max-width: 350px;">Employers will reach out to you here. You can also send follow-up messages to employers from your <strong>Applied Jobs</strong> log.</p>
                    <?php endif; ?>
                </div>
            <?php else : 
                $partner_info = get_userdata($active_chat);
                $thread_id = ($user_id < $active_chat) ? $user_id . '_' . $active_chat : $active_chat . '_' . $user_id;
                
                // Mark unread messages as read
                $unread_msgs = get_posts(['post_type' => 'nk_message', 'meta_query' => [['key' => '_thread_id', 'value' => $thread_id], ['key' => '_receiver_id', 'value' => $user_id], ['key' => '_is_read', 'value' => 0]], 'posts_per_page' => -1]);
                foreach ($unread_msgs as $um) { update_post_meta($um->ID, '_is_read', 1); }

                // Fetch full conversation history
                $messages = get_posts([
                    'post_type' => 'nk_message', 'posts_per_page' => 100, 
                    'meta_query' => [ 'relation' => 'AND', ['key' => '_thread_id', 'value' => $thread_id], ['key' => '_deleted_by_' . $user_id, 'compare' => 'NOT EXISTS'] ],
                    'orderby' => 'date', 'order' => 'ASC'
                ]);

                $show_paywall = ($active_view === 'employer' && !$is_premium);
            ?>
                <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff;">
                    <h3 style="margin: 0; font-size: 16px; color: #0f172a;"><?php echo esc_html($partner_info->display_name); ?></h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <?php if ($show_paywall): ?>
                            <span style="background: #fef08a; color: #854d0e; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Premium Lock 🔒</span>
                        <?php endif; ?>
                        <button id="nk-delete-chat-btn" title="Delete Conversation" style="background: #fff0f2; color: #ef4444; border: 1px solid #ffcdd2; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer;">🗑️ Clear Chat</button>
                    </div>
                </div>
                
                <div id="nk-chat-box" style="flex: 1; padding: 20px; overflow-y: auto; background: #f1f5f9; display: flex; flex-direction: column; gap: 15px;">
                    <?php if (empty($messages)) : ?>
                        <p style="text-align: center; color: #94a3b8; font-size: 13px; margin: auto;">This is the start of your conversation.</p>
                    <?php else : ?>
                        <?php foreach ($messages as $msg) : 
                            $is_me = ($msg->post_author == $user_id);
                            $align = $is_me ? 'flex-end' : 'flex-start';
                            $bg = $is_me ? '#0A66C2' : '#ffffff';
                            $color = $is_me ? '#ffffff' : '#334155';
                            $radius = $is_me ? '12px 12px 0 12px' : '12px 12px 12px 0';
                            $blur_css = ($show_paywall && !$is_me) ? 'filter: blur(4px); user-select: none;' : '';
                        ?>
                            <div style="align-self: <?php echo $align; ?>; max-width: 70%; display: flex; flex-direction: column;">
                                <div style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 12px 16px; border-radius: <?php echo $radius; ?>; font-size: 14px; line-height: 1.5; box-shadow: 0 2px 5px rgba(0,0,0,0.05); <?php echo $blur_css; ?>">
                                    <?php echo nl2br(esc_html($msg->post_content)); ?>
                                </div>
                                <span style="font-size: 11px; color: #94a3b8; margin-top: 5px; text-align: <?php echo $is_me ? 'right' : 'left'; ?>;">
                                    <?php echo get_the_time('M j, g:i a', $msg); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($show_paywall) : ?>
                        <div style="position: absolute; top: 60px; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.7); display: flex; justify-content: center; align-items: center; z-index: 10;">
                            <div style="background: #fff; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; max-width: 300px;">
                                <div style="font-size: 40px; margin-bottom: 10px;">🔒</div>
                                <h3 style="margin: 0 0 10px 0; color: #0f172a;">Upgrade to Reply</h3>
                                <p style="margin: 0 0 20px 0; font-size: 13px; color: #64748b;">Premium members can read and send unlimited messages directly to candidates.</p>
                                <a href="<?php echo site_url('/pricing/'); ?>" style="display: block; background: #10b981; color: #fff; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">Upgrade Now</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!$show_paywall) : ?>
                    <div style="padding: 20px; border-top: 1px solid #e2e8f0; background: #fff;">
                        <form id="nk-chat-form" style="display: flex; gap: 10px;">
                            <textarea name="message" required placeholder="Type your message..." style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-family: inherit; font-size: 14px; resize: none; height: 45px;"></textarea>
                            <input type="hidden" name="receiver_id" value="<?php echo esc_attr($active_chat); ?>">
                            <input type="hidden" name="action" value="nk_send_message">
                            <input type="hidden" name="security" value="<?php echo wp_create_nonce('nk_message_nonce'); ?>">
                            <button type="submit" style="background: #0A66C2; color: #fff; border: none; padding: 0 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Send</button>
                        </form>
                    </div>
                <?php endif; ?>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const chatBox = document.getElementById('nk-chat-box');
                        if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;

                        const chatForm = document.getElementById('nk-chat-form');
                        if(chatForm) {
                            chatForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                const btn = chatForm.querySelector('button');
                                btn.innerText = '...'; btn.disabled = true;
                                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: new FormData(this) })
                                .then(res => res.json()).then(data => {
                                    if(data.success) { window.location.reload(); } 
                                    else { alert(data.data || 'Failed to send'); btn.innerText = 'Send'; btn.disabled = false; }
                                });
                            });
                        }

                        const delBtn = document.getElementById('nk-delete-chat-btn');
                        if(delBtn) {
                            delBtn.addEventListener('click', function() {
                                if(confirm('Are you sure you want to clear this conversation?')) {
                                    let fd = new FormData();
                                    fd.append('action', 'nk_delete_conversation');
                                    fd.append('partner_id', '<?php echo esc_attr($active_chat); ?>');
                                    fd.append('security', '<?php echo wp_create_nonce("nk_message_nonce"); ?>');
                                    fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                                    .then(res => res.json()).then(data => { if(data.success) window.location.href = '?tab=messages'; });
                                }
                            });
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}