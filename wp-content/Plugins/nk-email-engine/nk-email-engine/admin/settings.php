<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$message = '';

if ( isset( $_POST['nk_settings_nonce'] ) && wp_verify_nonce( $_POST['nk_settings_nonce'], 'nk_save_provider_settings' ) ) {

    if ( isset( $_POST['save_ses'] ) ) {
        NK_Provider_Manager::save_credentials( 'amazon_ses', isset( $_POST['ses_access_key'] ) ? trim( wp_unslash( $_POST['ses_access_key'] ) ) : '', isset( $_POST['ses_secret_key'] ) ? trim( wp_unslash( $_POST['ses_secret_key'] ) ) : '', isset( $_POST['ses_region'] ) ? sanitize_text_field( wp_unslash( $_POST['ses_region'] ) ) : 'us-east-1' );
        $message = '<div class="notice notice-success is-dismissible" style="border-left-color:#10b981;"><p>✅ <strong>Success:</strong> Amazon SES credentials encrypted and saved.</p></div>';
    }

    if ( isset( $_POST['save_brevo'] ) ) {
        NK_Provider_Manager::save_credentials( 'brevo', isset( $_POST['brevo_api_key'] ) ? trim( wp_unslash( $_POST['brevo_api_key'] ) ) : '' );
        $message = '<div class="notice notice-success is-dismissible" style="border-left-color:#10b981;"><p>✅ <strong>Success:</strong> Brevo credentials encrypted and saved.</p></div>';
    }

    // 🛑 10X UPGRADE: Master Switch Handler
    if ( isset( $_POST['switch_provider'] ) ) {
        $choice = sanitize_text_field( wp_unslash( $_POST['switch_provider'] ) );
        
        if ( $choice === 'paused' ) {
            update_option('nk_kill_switch', 'yes');
            $message = '<div class="notice notice-error is-dismissible" style="border-left-color:#ef4444;"><p>🛑 <strong>KILL SWITCH ENGAGED:</strong> All outgoing emails are now frozen. The queue will hold emails safely until you resume.</p></div>';
        } else {
            update_option('nk_kill_switch', 'no');
            NK_Provider_Manager::switch_provider( $choice );
            $message = '<div class="notice notice-success is-dismissible" style="border-left-color:#10b981;"><p>🔄 <strong>System Resumed:</strong> Active routing provider switched to ' . esc_html(strtoupper($choice)) . '.</p></div>';
        }
    }
}

$active = NK_Provider_Manager::get_active_provider_name();
$ses    = NK_Provider_Amazon_SES::get_settings();
$brevo  = NK_Provider_Brevo::get_settings();

$ses_configured = (!empty($ses['api_key']) && !empty($ses['secret_key']));
$brevo_configured = (!empty($brevo['api_key']));
?>
<div class="wrap">
    <h1 style="font-weight: 800; border-left: 4px solid #0A66C2; padding-left: 15px; margin-bottom: 25px;">API Provider Settings</h1>
    
    <?php echo $message; ?>

    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid <?php echo $active === 'paused' ? '#ef4444' : '#0A66C2'; ?>; max-width: 800px;">
        <h2 style="margin-top: 0; padding:0; font-size:18px; display:flex; align-items:center; gap:8px;">
            🚦 Master Routing Switch 
            <?php if($active === 'paused'): ?>
                <span style="background:#fef2f2; border:1px solid #f87171; color:#b91c1c; padding:4px 12px; border-radius:12px; font-size:11px; font-weight:bold; text-transform:uppercase;">System Paused (Kill Switch Active)</span>
            <?php else: ?>
                <span style="background:#e2e8f0; padding:3px 10px; border-radius:12px; font-size:11px; text-transform:uppercase; color:#0f172a;">Active: <?php echo esc_html( ucfirst( str_replace( '_', ' ', $active ) ) ); ?></span>
            <?php endif; ?>
        </h2>
        <p style="color:#64748b; font-size:14px; margin-bottom:20px;">Select which API provider the system should use to dispatch queued and instant emails. Use the Kill Switch to instantly freeze all outgoing emails.</p>
        
        <form method="post" style="display:flex; gap:15px; flex-wrap: wrap;">
            <?php wp_nonce_field( 'nk_save_provider_settings', 'nk_settings_nonce' ); ?>
            
            <button type="submit" name="switch_provider" value="amazon_ses" class="button <?php echo 'amazon_ses' === $active ? 'button-primary' : 'button-secondary'; ?>" style="<?php echo 'amazon_ses' === $active ? 'background:#0A66C2; border-color:#0A66C2;' : ''; ?> padding:6px 24px; height:auto; font-weight:bold;">Route via Amazon SES</button>
            
            <button type="submit" name="switch_provider" value="brevo" class="button <?php echo 'brevo' === $active ? 'button-primary' : 'button-secondary'; ?>" style="<?php echo 'brevo' === $active ? 'background:#0A66C2; border-color:#0A66C2;' : ''; ?> padding:6px 24px; height:auto; font-weight:bold;">Route via Brevo</button>
            
            <div style="width:100%; height:1px; background:#e2e8f0; margin:5px 0;"></div>
            
            <button type="submit" name="switch_provider" value="paused" class="button" style="background:<?php echo $active === 'paused' ? '#991b1b' : '#ef4444'; ?>; color:white; border-color:transparent; padding:6px 24px; height:auto; font-weight:bold; text-shadow:none;" onclick="return confirm('WARNING: You are about to pause the entire email engine. No emails will go out until you select a provider again. Proceed?');">🛑 ENGAGE KILL SWITCH (Pause All Sending)</button>
        </form>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; max-width: 1200px;">
        
        <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid <?php echo $ses_configured ? '#10b981' : '#f59e0b'; ?>;">
            <h2 style="margin-top: 0; padding:0; font-size:18px;">Amazon SES Credentials</h2>
            <p style="color:#64748b; font-size:13px;">Uses high-performance AWS Signature V4 REST connection.</p>
            
            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:6px; margin-bottom:20px; font-size:12px; color:#334155;">
                <strong>⚠️ HTTP 403 Checklist:</strong><br>
                1. Ensure your AWS IAM User has the <code>ses:SendEmail</code> permission.<br>
                2. Ensure your WordPress Admin Email (<strong><?php echo esc_html(get_option('admin_email')); ?></strong>) is verified in the AWS SES Dashboard.
            </div>

            <form method="post">
                <?php wp_nonce_field( 'nk_save_provider_settings', 'nk_settings_nonce' ); ?>
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">AWS Access Key ID</label>
                    <input type="text" name="ses_access_key" value="<?php echo esc_attr( $ses['api_key'] ); ?>" style="width:100%; padding:8px;" autocomplete="off">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">AWS Secret Access Key</label>
                    <input type="password" name="ses_secret_key" value="<?php echo esc_attr( $ses['secret_key'] ); ?>" style="width:100%; padding:8px;" autocomplete="off" placeholder="Leaves blank if empty">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">AWS Region</label>
                    <input type="text" name="ses_region" value="<?php echo esc_attr( $ses['region'] ); ?>" style="width:100%; padding:8px;" placeholder="e.g. us-east-1 or eu-west-2">
                </div>
                <button type="submit" name="save_ses" class="button button-primary" style="background:#0f172a; border-color:#0f172a;">Save AES-256 Encrypted Keys</button>
            </form>
        </div>

        <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid <?php echo $brevo_configured ? '#10b981' : '#f59e0b'; ?>;">
            <h2 style="margin-top: 0; padding:0; font-size:18px;">Brevo (Sendinblue) Credentials</h2>
            <p style="color:#64748b; font-size:13px;">Uses Brevo v3 Transactional API.</p>
            
            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:6px; margin-bottom:20px; font-size:12px; color:#334155;">
                <strong>⚠️ Delivery Note:</strong><br>
                If the plugin says "Success" but you do not receive the email, you MUST log into Brevo and verify your sender domain and sender email (<strong><?php echo esc_html(get_option('admin_email')); ?></strong>).
            </div>

            <form method="post">
                <?php wp_nonce_field( 'nk_save_provider_settings', 'nk_settings_nonce' ); ?>
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Brevo API Key (v3)</label>
                    <input type="password" name="brevo_api_key" value="<?php echo esc_attr( $brevo['api_key'] ); ?>" style="width:100%; padding:8px;" autocomplete="off" placeholder="xkeysib-...">
                </div>
                <button type="submit" name="save_brevo" class="button button-primary" style="background:#0f172a; border-color:#0f172a;">Save AES-256 Encrypted Key</button>
            </form>
        </div>

    </div>
</div>