<?php
if (!defined('ABSPATH')) exit;

// Global Floating Chat Widget HTML
add_action('wp_footer', 'nk_floating_chat_widget_html');
function nk_floating_chat_widget_html() {
    // Hide widget if already on the messages tab
    if ( isset($_GET['tab']) && $_GET['tab'] === 'messages' ) return;

    $is_logged_in = is_user_logged_in();
    $unread_count = 0;
    
    // Default dashboard URL for logged-in users (fallback to candidate)
    $inbox_url = site_url('/candidate-dashboard/?tab=messages');
    
    if ( $is_logged_in ) {
        // Fetch unread messages count
        if ( function_exists('nk_get_unread_message_count') ) {
            $unread_count = nk_get_unread_message_count(get_current_user_id());
        }
        
        // Dynamically change URL if the user is an Employer
        $current_user = wp_get_current_user();
        if ( in_array('nkrp_employer', (array) $current_user->roles) ) {
            $inbox_url = site_url('/employer-dashboard/?tab=messages');
        }
    }
    ?>
    <style>
        @media (max-width: 768px) {
            .nk-floating-chat-container { display: none !important; }
        }
    </style>
    <div class="nk-floating-chat-container">
        <div class="nk-chat-panel" id="nk-chat-panel">
            <div class="nk-chat-header">
                <div class="nk-chat-close" onclick="document.getElementById('nk-chat-panel').classList.remove('active')">&#10005;</div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color:#fff;">&#128172; NatunKicho Inbox</h3>
                <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.9; color:#fff;">Connect with the hospitality network.</p>
            </div>
            
            <div class="nk-chat-body">
                <?php if ( $is_logged_in ) : ?>
                    <div style="font-size: 40px; margin-bottom: 10px;">&#128229;</div>
                    <h4 style="margin: 0 0 10px 0; color: #0f172a; font-size: 18px;">Your Conversations</h4>
                    <p style="margin: 0 0 20px 0; color: #64748b; font-size: 14px;">
                        <?php echo $unread_count > 0 ? "You have <strong>{$unread_count} unread</strong> messages waiting for you." : "Your inbox is up to date."; ?>
                    </p>
                    
                    <!-- DYNAMIC INBOX URL BUTTON -->
                    <a href="<?php echo esc_url($inbox_url); ?>" style="display: block; width: 100%; background: #0A66C2; color: #fff; text-decoration: none; padding: 12px 0; border-radius: 8px; font-weight: bold; font-size: 14px; transition: background 0.2s; text-align: center;">Open Full Inbox &rarr;</a>
                
                <?php else : ?>
                    <div style="font-size: 40px; margin-bottom: 10px;">&#128274;</div>
                    <h4 style="margin: 0 0 10px 0; color: #0f172a; font-size: 18px;">Sign In to Chat</h4>
                    <p style="margin: 0 0 20px 0; color: #64748b; font-size: 14px;">Log in to your account to instantly message top employers and candidates.</p>
                    
                    <!-- LOGIN WITH REDIRECT BACK TO CURRENT PAGE -->
                    <a href="<?php echo esc_url(site_url('/login/?redirect_to=' . urlencode($_SERVER['REQUEST_URI']))); ?>" style="display: block; width: 100%; background: #10b981; color: #fff; text-decoration: none; padding: 12px 0; border-radius: 8px; font-weight: bold; font-size: 14px; text-align: center;">Sign In / Register</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="nk-chat-bubble" onclick="document.getElementById('nk-chat-panel').classList.toggle('active')">
            <span class="nk-chat-icon">&#128172;</span>
            <?php if ( $unread_count > 0 ) : ?>
                <span class="nk-chat-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}