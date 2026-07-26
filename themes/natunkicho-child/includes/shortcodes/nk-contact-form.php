<?php
/**
 * Natun Kicho – AJAX Contact Form
 * Shortcode: [nk_contact_form]
 */

if (!defined('ABSPATH')) exit;

/**
 * GOOGLE SCRIPT URL
 */
if (!defined('NK_CONTACT_GOOGLE_API_URL')) {
    define('NK_CONTACT_GOOGLE_API_URL', 'https://script.google.com/macros/s/AKfycbzkEReFx0y-mnozpNROTCiLSn_YJ4H1dVvfcpkSa1JWshpuKaMkyMX-pfF0X9D2QBg4/exec');
}

/**
 * REGISTER ASSETS
 */
function nk_contact_form_register_assets() {
    wp_register_style(
        'nk-contact-form',
        get_stylesheet_directory_uri() . '/assets/css/nk-contact-form.css',
        array(),
        '1.0'
    );

    wp_register_script(
        'nk-contact-form',
        get_stylesheet_directory_uri() . '/assets/js/nk-contact-form.js',
        array('jquery'),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'nk_contact_form_register_assets');


/**
 * SHORTCODE OUTPUT
 */
function nk_contact_form_shortcode($atts = array()) {
    wp_enqueue_style('nk-contact-form');
    wp_enqueue_script('nk-contact-form');

    // FIXED: Use site_url() instead of admin_url() for better compatibility
    wp_localize_script('nk-contact-form', 'nkContactVars', array(
        'ajax_url' => esc_url_raw(admin_url('admin-ajax.php')),
        'nonce'    => wp_create_nonce('nk_contact_nonce'),
        'site_url' => esc_url_raw(get_site_url())
    ));

    // Math captcha
    $a = rand(1, 9);
    $b = rand(1, 9);
    $question = "$a + $b = ?";
    $math_token = wp_create_nonce("nk_math_{$a}_{$b}");

    ob_start();
    ?>

    <form id="nk-contact-form" class="nk-contact-form" method="post">
        <div class="nk-field">
            <label>Name</label>
            <input type="text" id="nk_name" name="name">
        </div>

        <div class="nk-field">
            <label>Email *</label>
            <input type="email" id="nk_email" name="email" required>
        </div>

        <div class="nk-field">
            <label>Subject</label>
            <input type="text" id="nk_subject" name="subject">
        </div>

        <div class="nk-field">
            <label>Whatsapp Number</label>
            <input type="text" id="nk_whatsapp" name="whatsapp">
        </div>

        <div class="nk-field">
            <label>Topic</label>
            <select id="nk_topic" name="topic">
                <option value="Preopening">Preopening</option>
                <option value="Training">Training</option>
                <option value="Consulting">Consulting</option>
                <option value="General Inquiry">General Inquiry</option>
            </select>
        </div>

        <div class="nk-field">
            <label>Message</label>
            <textarea id="nk_message" name="message" rows="5"></textarea>
        </div>

        <!-- Honeypot -->
        <input type="text" name="nk_hp" class="nk-hp" tabindex="-1" autocomplete="off">

        <!-- Captcha -->
        <div class="nk-field">
            <label>Security: <?php echo esc_html($question); ?></label>
            <input type="text" id="nk_math" name="nk_math" required>
            <input type="hidden" name="nk_math_token" value="<?php echo esc_attr($math_token); ?>">
        </div>

        <button type="submit" id="nk-contact-submit">Submit</button>

        <div id="nk-contact-result" class="nk-contact-result"></div>
    </form>

    <?php
    return ob_get_clean();
}
add_shortcode('nk_contact_form', 'nk_contact_form_shortcode');


/**
 * AJAX HANDLER
 */
function nk_handle_contact_form() {
    // Check if it's an AJAX request
    if (!wp_doing_ajax()) {
        wp_die('Invalid request', 400);
    }

    // Verify nonce
    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nk_contact_nonce')) {
        wp_send_json_error(['message' => 'Security verification failed. Please refresh the page.']);
    }

    // Honeypot check
    if (!empty($_POST['nk_hp'])) {
        wp_send_json_error(['message' => 'Invalid submission.']);
    }

    // Sanitize inputs
    $name     = sanitize_text_field($_POST['name'] ?? '');
    $email    = sanitize_email($_POST['email'] ?? '');
    $subject  = sanitize_text_field($_POST['subject'] ?? 'New Message');
    $whatsapp = sanitize_text_field($_POST['whatsapp'] ?? '');
    $topic    = sanitize_text_field($_POST['topic'] ?? '');
    $message  = wp_kses_post($_POST['message'] ?? '');
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '';

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Please enter a valid email address.']);
    }

    // Math captcha validation
    $math_input = trim($_POST['nk_math'] ?? '');
    $math_token = sanitize_text_field($_POST['nk_math_token'] ?? '');

    $valid_math = false;
    for ($a = 1; $a <= 9; $a++) {
        for ($b = 1; $b <= 9; $b++) {
            if (wp_verify_nonce($math_token, "nk_math_{$a}_{$b}")) {
                if ((string)($a + $b) === $math_input) {
                    $valid_math = true;
                    break 2;
                }
            }
        }
    }

    if (!$valid_math) {
        wp_send_json_error(['message' => 'Incorrect security answer. Please try again.']);
    }

    // Prepare email
    $to = "info@natunkicho.com";
    $email_subject = "New Contact Form: " . ($subject ?: 'General Inquiry');
    
    $body = sprintf(
        '<h3>New Contact Form Submission</h3>
        <p><strong>Name:</strong> %s</p>
        <p><strong>Email:</strong> %s</p>
        <p><strong>Whatsapp:</strong> %s</p>
        <p><strong>Topic:</strong> %s</p>
        <p><strong>Message:</strong></p>
        <p>%s</p>
        <hr>
        <p><em>IP Address:</em> %s<br>
        <em>Time:</em> %s</p>',
        esc_html($name),
        esc_html($email),
        esc_html($whatsapp),
        esc_html($topic),
        nl2br(esc_html($message)),
        esc_html($ip),
        date('Y-m-d H:i:s')
    );

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . $email . '>',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );

    // Send email
    $email_sent = wp_mail($to, $email_subject, $body, $headers);

    // Send to Google Sheets (non-blocking)
    $google_response = wp_remote_post(NK_CONTACT_GOOGLE_API_URL, [
        'method' => 'POST',
        'timeout' => 15,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode([
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'whatsapp' => $whatsapp,
            'topic' => $topic,
            'message' => $message,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => date('Y-m-d H:i:s')
        ]),
    ]);

    if ($email_sent) {
        wp_send_json_success(['message' => 'Thank you — your message was sent successfully.']);
    } else {
        wp_send_json_error(['message' => 'Email could not be sent. Please try again later.']);
    }
}

add_action('wp_ajax_nopriv_nk_contact_submit', 'nk_handle_contact_form');
add_action('wp_ajax_nk_contact_submit', 'nk_handle_contact_form');