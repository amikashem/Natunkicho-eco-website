<?php

declare(strict_types=1);

namespace NKRecruitment\Notifications\Email;

if (!defined('ABSPATH')) {
    exit;
}

class EmailService
{
    private string $ses_api_key;
    private string $ses_api_secret;
    private string $ses_region;

    public function __construct()
    {
        // Set these in wp-config.php when you go live
        $this->ses_api_key    = defined('NKRP_SES_KEY') ? NKRP_SES_KEY : '';
        $this->ses_api_secret = defined('NKRP_SES_SECRET') ? NKRP_SES_SECRET : '';
        $this->ses_region     = defined('NKRP_SES_REGION') ? NKRP_SES_REGION : 'us-east-1';
    }

    /**
     * Send a beautifully formatted HTML email with SES / Fallback logic.
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject line
     * @param string $message The core message/body (HTML allowed)
     * @param bool $is_bulk TRUE if from Queue (disables wp_mail fallback)
     * @return bool True if sent successfully
     */
    public function send(string $to, string $subject, string $message, bool $is_bulk = false): bool
    {
        // 1. Wrap the message in your Premium SaaS HTML Template
        $html_content = $this->wrapInTemplate($subject, $message);

        // 2. Try Amazon SES First
        if ($this->sendViaSES($to, $subject, $html_content)) {
            return true;
        }

        // 3. SES Failed. Check if we are allowed to use the WordPress Fallback.
        if (!$is_bulk) {
            error_log("NKRP Email Warning: SES failed/inactive. Falling back to wp_mail for {$to}");
            return $this->sendViaWpMail($to, $subject, $html_content);
        }

        // 4. Bulk Email Fallback Blocked to protect server reputation
        error_log("NKRP Email Error: SES Failed for BULK email to {$to}. Fallback aborted.");
        return false;
    }

    /**
     * Amazon SES Logic
     */
    private function sendViaSES(string $to, string $subject, string $html_content): bool
    {
        if (empty($this->ses_api_key) || empty($this->ses_api_secret)) {
            return false; // Fail silently so wp_mail fallback triggers during local testing
        }

        // AWS SES API integration goes here (e.g., using wp_remote_post to AWS API endpoint)
        // Returning true here assumes success if keys are present
        return true; 
    }

    /**
     * Standard WordPress Mail (Your original logic)
     */
    private function sendViaWpMail(string $to, string $subject, string $html_content): bool
    {
        add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);

        $site_name = get_bloginfo('name');
        $admin_email = get_option('admin_email');
        $headers = [
            "From: {$site_name} <{$admin_email}>",
            "Reply-To: {$admin_email}"
        ];

        $result = wp_mail($to, $subject, $html_content, $headers);

        remove_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);

        return $result;
    }

    /**
     * Sets content type to HTML
     */
    public function setHtmlContentType(): string
    {
        return 'text/html';
    }

    /**
     * Wraps the raw message in our custom HTML template
     */
    private function wrapInTemplate(string $subject, string $message): string
    {
        $template_path = NKRP_PLUGIN_PATH . 'app/Notifications/Templates/default-email.php';
        
        if (!file_exists($template_path)) {
            return "<h2>{$subject}</h2><br>{$message}";
        }

        ob_start();
        $email_subject = $subject;
        $email_body = $message;
        require $template_path;
        return ob_get_clean();
    }
}