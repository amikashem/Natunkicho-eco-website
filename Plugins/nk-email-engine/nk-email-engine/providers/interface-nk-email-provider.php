<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Every provider (SES, Brevo, and future SendGrid/Mailgun/Postmark) implements
 * this so the queue processor can swap providers without knowing internals.
 */
interface NK_Email_Provider_Interface {

    /**
     * @param string $to_email
     * @param string $to_name
     * @param string $subject
     * @param string $html_body
     * @return array{success: bool, message_id: string, error: string}
     */
    public function send( $to_email, $to_name, $subject, $html_body );

    /** Returns true if API credentials are present and the provider is ready to send. */
    public function is_configured();

    /** Machine-readable name, e.g. 'amazon_ses', 'brevo'. */
    public function get_name();
}
