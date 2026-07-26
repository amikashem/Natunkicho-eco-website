<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$sub_counts   = NK_Subscriber_Manager::count_by_status();
$queue_counts = NK_Email_Queue::get_queue_counts();
$summary      = class_exists('NK_Email_Logger') ? NK_Email_Logger::get_summary( 30 ) : array('total_sent'=>0, 'total_opened'=>0, 'total_clicked'=>0, 'total_bounced'=>0, 'total_complaints'=>0);
$active_prov  = NK_Provider_Manager::get_active_provider_name();

// Calculate total for quick check
$total_subs = array_sum($sub_counts);
?>
<div class="wrap nk-dashboard" style="max-width: 1200px;">
    <h1 style="font-weight: 800; color: #0f172a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
        <span style="background: #0A66C2; color: #fff; padding: 5px 10px; border-radius: 6px; font-size: 18px;">NK</span> 
        Email Intelligence Engine
    </h1>

    <?php if ( $total_subs === 0 ) : ?>
        <div style="background: #fffbeb; border: 1px solid #f59e0b; border-left: 5px solid #f59e0b; padding: 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 5px 0; color: #92400e;">⚠️ Database Not Synchronized</h3>
                <p style="margin: 0; color: #b45309; font-size: 14px;">Your custom email database is currently empty. You need to sync your existing WordPress users (Employers & Candidates) into the mailing engine.</p>
            </div>
            <a href="?page=nk-email-subscribers" class="button button-primary" style="background: #f59e0b; border: none;">Go to Sync Tool &rarr;</a>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #64748b; text-transform: uppercase;">Active Router</h3>
            <p style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">
                <?php echo esc_html( $active_prov === 'ses' || $active_prov === 'amazon_ses' ? 'Amazon SES' : 'Brevo' ); ?>
            </p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #10b981; font-weight: bold;">● Online & Routing</p>
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #64748b; text-transform: uppercase;">Total Audience</h3>
            <p style="font-size: 28px; font-weight: 800; color: #0A66C2; margin: 0;">
                <?php echo esc_html( $total_subs ); ?>
            </p>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #64748b;">
                <span>Active: <strong><?php echo esc_html( $sub_counts['active'] ?? 0 ); ?></strong></span>
                <span>Unsubbed: <strong style="color: #ef4444;"><?php echo esc_html( $sub_counts['unsubscribed'] ?? 0 ); ?></strong></span>
            </div>
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #64748b; text-transform: uppercase;">Pending in Queue</h3>
            <p style="font-size: 28px; font-weight: 800; color: #f59e0b; margin: 0;">
                <?php echo esc_html( $queue_counts['pending'] ?? 0 ); ?>
            </p>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #64748b;">
                <span>Processing: <strong><?php echo esc_html( $queue_counts['processing'] ?? 0 ); ?></strong></span>
                <span>Failed: <strong style="color: #ef4444;"><?php echo esc_html( $queue_counts['failed'] ?? 0 ); ?></strong></span>
            </div>
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #64748b; text-transform: uppercase;">Sent (Last 30 Days)</h3>
            <p style="font-size: 28px; font-weight: 800; color: #10b981; margin: 0;">
                <?php echo esc_html( $summary['total_sent'] ?? 0 ); ?>
            </p>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #64748b;">
                <span>Bounced: <strong style="color: #ef4444;"><?php echo esc_html( $summary['total_bounced'] ?? 0 ); ?></strong></span>
                <span>Complaints: <strong style="color: #ef4444;"><?php echo esc_html( $summary['total_complaints'] ?? 0 ); ?></strong></span>
            </div>
        </div>
    </div>

    <div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 25px; border-radius: 12px; margin-bottom: 30px;">
        <h2 style="margin-top: 0; font-size: 18px; color: #0f172a;">⚡ Quick Actions</h2>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="?page=nk-email-campaigns" class="button button-primary button-large" style="background: #0A66C2; border-color: #0A66C2;">+ Create Campaign</a>
            <a href="?page=nk-email-subscribers" class="button button-secondary button-large">Manage Subscribers & Sync</a>
            <a href="?page=nk-email-queue" class="button button-secondary button-large">View Queue Engine</a>
            <a href="?page=nk-email-settings" class="button button-secondary button-large">API Settings</a>
        </div>
    </div>
</div>