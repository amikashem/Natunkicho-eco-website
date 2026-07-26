<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$queue = NK_Email_Queue::get_recent( 100 );
?>
<div class="wrap">
    <h1>Email Queue</h1>
    <p>The cron worker (<code>nk_process_email_queue</code>) runs every minute and sends up to 50 emails per run, waiting 60 seconds between batches to protect server load and sender reputation.</p>

    <table class="widefat striped">
        <thead><tr><th>Recipient</th><th>Subject</th><th>Provider</th><th>Priority</th><th>Status</th><th>Retries</th><th>Scheduled</th><th>Sent</th></tr></thead>
        <tbody>
        <?php if ( $queue ) : foreach ( $queue as $q ) : ?>
            <tr>
                <td><?php echo esc_html( $q['recipient_name'] . ' <' . $q['recipient_email'] . '>' ); ?></td>
                <td><?php echo esc_html( $q['subject'] ); ?></td>
                <td><?php echo esc_html( $q['provider'] ? $q['provider'] : '—' ); ?></td>
                <td><?php echo esc_html( $q['priority'] ); ?></td>
                <td><span class="nk-status nk-status-<?php echo esc_attr( $q['status'] ); ?>"><?php echo esc_html( $q['status'] ); ?></span></td>
                <td><?php echo esc_html( $q['retry_count'] ); ?></td>
                <td><?php echo esc_html( $q['scheduled_at'] ); ?></td>
                <td><?php echo esc_html( $q['sent_at'] ? $q['sent_at'] : '—' ); ?></td>
            </tr>
        <?php endforeach; else : ?>
            <tr><td colspan="8">Queue is empty.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
