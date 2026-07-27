<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$days    = isset( $_GET['days'] ) ? (int) $_GET['days'] : 30;
$summary = NK_Email_Logger::get_summary( $days );
$recent  = NK_Email_Logger::get_recent( 100 );

$open_rate  = $summary['total_sent'] > 0 ? round( ( $summary['total_opened'] / $summary['total_sent'] ) * 100, 1 ) : 0;
$click_rate = $summary['total_sent'] > 0 ? round( ( $summary['total_clicked'] / $summary['total_sent'] ) * 100, 1 ) : 0;
?>
<div class="wrap">
    <h1>Analytics</h1>

    <ul class="subsubsub">
        <li><a href="?page=nk-email-analytics&days=7">Last 7 days</a> |</li>
        <li><a href="?page=nk-email-analytics&days=30">Last 30 days</a> |</li>
        <li><a href="?page=nk-email-analytics&days=90">Last 90 days</a></li>
    </ul>

    <div class="nk-cards">
        <div class="nk-card"><h3>Total Sent</h3><p class="nk-card-value"><?php echo esc_html( $summary['total_sent'] ); ?></p></div>
        <div class="nk-card"><h3>Open Rate</h3><p class="nk-card-value"><?php echo esc_html( $open_rate ); ?>%</p></div>
        <div class="nk-card"><h3>Click Rate</h3><p class="nk-card-value"><?php echo esc_html( $click_rate ); ?>%</p></div>
        <div class="nk-card"><h3>Bounced</h3><p class="nk-card-value"><?php echo esc_html( $summary['total_bounced'] ); ?></p></div>
        <div class="nk-card"><h3>Complaints</h3><p class="nk-card-value"><?php echo esc_html( $summary['total_complaints'] ); ?></p></div>
    </div>

    <h2>Recent Delivery Log</h2>
    <table class="widefat striped">
        <thead><tr><th>Recipient</th><th>Provider</th><th>Status</th><th>Opened</th><th>Clicked</th><th>Bounced</th><th>Complaint</th><th>Date</th></tr></thead>
        <tbody>
        <?php if ( $recent ) : foreach ( $recent as $r ) : ?>
            <tr>
                <td><?php echo esc_html( $r['recipient_email'] ); ?></td>
                <td><?php echo esc_html( $r['provider'] ); ?></td>
                <td><?php echo esc_html( $r['status'] ); ?></td>
                <td><?php echo $r['opened'] ? '✔' : '—'; ?></td>
                <td><?php echo $r['clicked'] ? '✔' : '—'; ?></td>
                <td><?php echo $r['bounced'] ? '✔' : '—'; ?></td>
                <td><?php echo $r['complaint'] ? '✔' : '—'; ?></td>
                <td><?php echo esc_html( $r['created_at'] ); ?></td>
            </tr>
        <?php endforeach; else : ?>
            <tr><td colspan="8">No data yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
