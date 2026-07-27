<?php if (!defined('ABSPATH')) exit; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= esc_html($email_subject) ?></title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .email-container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .email-header { background-color: #0f172a; padding: 30px; text-align: center; }
        .email-header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .email-body { padding: 40px; color: #334155; font-size: 16px; line-height: 1.6; }
        .email-footer { background-color: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 13px; }
        .email-button { display: inline-block; background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1><?= esc_html(get_bloginfo('name')) ?></h1>
        </div>
        
        <div class="email-body">
            <?= wp_kses_post($email_body) ?>
        </div>
        
        <div class="email-footer">
            &copy; <?= date('Y') ?> <?= esc_html(get_bloginfo('name')) ?>. All rights reserved.<br>
            <?php esc_html_e('This is an automated message, please do not reply directly to this email.', 'nk-recruitment'); ?>
        </div>
    </div>
</body>
</html>