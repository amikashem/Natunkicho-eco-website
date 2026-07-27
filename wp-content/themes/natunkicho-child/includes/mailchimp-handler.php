<?php
/**
 * Mailchimp Subscription Handler & Frontend UI
 * Safe and secure API integration
 */

if (!defined('ABSPATH')) exit;

class NK_Mailchimp_Handler {
    
    private $api_key;
    private $audience_id;
    private $data_center;
    
    public function __construct() {
        // Set your Mailchimp credentials here
        $this->api_key = 'XXXXXXXXXXXXXXXXXXXX'; 
        $this->audience_id = 'XXXXX'; 
        $this->data_center = substr($this->api_key, strpos($this->api_key, '-') + 1);
        
        add_action('wp_ajax_nk_subscribe_email', array($this, 'handle_subscription'));
        add_action('wp_ajax_nopriv_nk_subscribe_email', array($this, 'handle_subscription'));
    }
    
    public function handle_subscription() {
        // Security check
        check_ajax_referer('nk_subscribe_nonce', 'security');
        
        // Validate email
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (!is_email($email)) {
            wp_send_json_error('Please enter a valid email address.');
        }
        
        // Prepare data for Mailchimp
        $data = array(
            'email_address' => $email,
            'status' => 'pending', // double opt-in
            'tags' => array('Website Subscriber')
        );
        
        $result = $this->add_subscriber_to_mailchimp($data);
        
        if ($result['success']) {
            wp_send_json_success('Thank you! Please check your email to confirm your subscription.');
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    private function add_subscriber_to_mailchimp($data) {
        $url = "https://{$this->data_center}.api.mailchimp.com/3.0/lists/{$this->audience_id}/members/";
        $body = json_encode($data);
        
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('anystring:' . $this->api_key)
            ),
            'body' => $body,
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => 'Connection error. Please try again.');
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code === 200) {
            return array('success' => true, 'message' => 'Subscribed successfully!');
        } elseif ($response_code === 400 && isset($response_body['title']) && $response_body['title'] === 'Member Exists') {
            return array('success' => true, 'message' => 'You are already subscribed!');
        } else {
            return array('success' => false, 'message' => 'Subscription failed. Please try again later.');
        }
    }
}

// Initialize the handler
new NK_Mailchimp_Handler();


/**
 * =========================================================================
 * FRONTEND SUBSCRIPTION WIDGET UI (Shortcode: [nk_job_alert_subscribe])
 * =========================================================================
 */
function nk_job_alert_subscribe_shortcode() {
    ob_start();
    ?>
    <div class="nk-subscribe-widget" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #0f172a; font-weight: 800;">Get Instant Job Alerts 📩</h3>
        <p style="margin: 0 0 15px 0; font-size: 13px; color: #64748b; line-height: 1.5;">Don't miss out on top hospitality roles. Enter your email to receive weekly updates directly to your inbox.</p>
        
        <form id="nk-subscribe-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="email" name="email" required placeholder="Enter your email address" style="flex: 1; min-width: 200px; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none;">
            <input type="hidden" name="action" value="nk_subscribe_email">
            <input type="hidden" name="security" value="<?php echo wp_create_nonce('nk_subscribe_nonce'); ?>">
            <button type="submit" style="background: #0A66C2; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer; transition: background 0.2s;">Subscribe</button>
        </form>
        <div id="nk-subscribe-msg" style="margin-top: 10px; font-size: 13px; font-weight: bold; display: none;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const subForm = document.getElementById('nk-subscribe-form');
        const subMsg = document.getElementById('nk-subscribe-msg');
        const subBtn = subForm ? subForm.querySelector('button') : null;

        if (subForm) {
            subForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const originalText = subBtn.innerText;
                subBtn.innerText = 'Sending...';
                subBtn.disabled = true;
                subMsg.style.display = 'none';

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.json())
                .then(data => {
                    subMsg.style.display = 'block';
                    if (data.success) {
                        subMsg.style.color = '#10b981';
                        subMsg.innerText = data.data;
                        subForm.reset();
                    } else {
                        subMsg.style.color = '#ef4444';
                        subMsg.innerText = data.data;
                    }
                    subBtn.innerText = originalText;
                    subBtn.disabled = false;
                }).catch(err => {
                    subMsg.style.display = 'block';
                    subMsg.style.color = '#ef4444';
                    subMsg.innerText = 'Network error. Try again.';
                    subBtn.innerText = originalText;
                    subBtn.disabled = false;
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_job_alert_subscribe', 'nk_job_alert_subscribe_shortcode');