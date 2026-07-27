<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table = NK_Database::table( 'suppression_list' );

if ( isset( $_POST['nk_suppress_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nk_suppress_nonce'] ) ), 'nk_add_suppression' ) ) {
    $email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : 'manual';
    if ( is_email( $email ) ) {
        NK_Subscriber_Manager::suppress( $email, $reason );
        echo '<div class="notice notice-success"><p>Added to suppression list.</p></div>';
    }
}

if ( isset( $_GET['remove'] ) ) {
    $wpdb->delete( $table, array( 'id' => (int) $_GET['remove'] ) );
    echo '<div class="notice notice-success"><p>Removed from suppression list.</p></div>';
}

$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );
?>
<div class="wrap">
    <h1>Suppression List</h1>
    <p>Addresses here are never re-added to the email queue, regardless of campaign or subscriber status — checked at both enqueue-time and send-time.</p>

    <form method="post" class="nk-inline-form">
        <?php wp_nonce_field( 'nk_add_suppression', 'nk_suppress_nonce' ); ?>
        <input type="email" name="email" placeholder="Email to suppress" required>
        <select name="reason">
            <option value="manual">Manual</option>
            <option value="bounced">Bounced</option>
            <option value="complaint">Complaint</option>
            <option value="unsubscribed">Unsubscribed</option>
        </select>
        <button class="button button-primary" type="submit">Add</button>
    </form>

    <table class="widefat striped">
        <thead><tr><th>Email</th><th>Reason</th><th>Added</th><th></th></tr></thead>
        <tbody>
        <?php if ( $rows ) : foreach ( $rows as $r ) : ?>
            <tr>
                <td><?php echo esc_html( $r['email'] ); ?></td>
                <td><?php echo esc_html( $r['reason'] ); ?></td>
                <td><?php echo esc_html( $r['created_at'] ); ?></td>
                <td><a href="?page=nk-email-suppression&remove=<?php echo esc_attr( $r['id'] ); ?>" onclick="return confirm('Remove this suppression entry?');">Remove</a></td>
            </tr>
        <?php endforeach; else : ?>
            <tr><td colspan="4">List is empty.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
