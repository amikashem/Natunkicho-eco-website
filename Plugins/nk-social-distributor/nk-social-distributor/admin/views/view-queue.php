<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'nk_social_queue';

// Fetch the latest 50 items from the queue
$queue_items = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY scheduled_time DESC LIMIT 50" );
?>

<div style="display:flex; justify-content: space-between; align-items: center;">
        <h2>Live Social Publish Queue</h2>
        <a href="<?php echo admin_url('admin-post.php?action=nk_force_process_queue'); ?>" class="button button-primary" style="background:#2271b1; font-weight:bold;">🚀 Process Queue Now</a>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 80px;">ID</th>
                <th>Content Title</th>
                <th>Platform</th>
                <th>Status</th>
                <th>Scheduled Time</th>
                <th>Error / Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $queue_items ) ) : ?>
                <tr>
                    <td colspan="6">No items currently in the queue. Go publish a Job or Blog post!</td>
                </tr>
            <?php else : ?>
                <?php foreach ( $queue_items as $item ) : 
                    // Get the Post Title dynamically
                    $post_title = get_the_title( $item->content_id );
                    if ( empty($post_title) ) { $post_title = "Post Deleted (#{$item->content_id})"; }
                    
                    // Create beautiful badges for the status
                    $status_color = '#646970'; // Default gray
                    if ( $item->status === 'pending' ) {
                        $status_color = '#d63638'; // Red-ish waiting
                        $badge = '<span style="background:#fff8e5; color:#856404; padding:3px 8px; border-radius:12px; font-weight:bold; border:1px solid #ffeeba;">⏳ Pending</span>';
                    } elseif ( $item->status === 'published' ) {
                        $badge = '<span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:12px; font-weight:bold; border:1px solid #c3e6cb;">✅ Published</span>';
                    } elseif ( $item->status === 'failed' ) {
                        $badge = '<span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:12px; font-weight:bold; border:1px solid #f5c6cb;">❌ Failed</span>';
                    } else {
                        $badge = esc_html( ucfirst( $item->status ) );
                    }
                ?>
                    <tr>
                        <td><strong>#<?php echo esc_html( $item->id ); ?></strong></td>
                        <td>
                            <a href="<?php echo get_edit_post_link( $item->content_id ); ?>" target="_blank">
                                <strong><?php echo esc_html( $post_title ); ?></strong>
                            </a>
                            <br><small style="color:#646970;"><?php echo esc_html( ucfirst($item->content_type) ); ?></small>
                        </td>
                        <td>
                            <?php 
                                // Simple Platform Icons
                                if ( $item->platform == 'telegram' ) echo '✈️ Telegram';
                                elseif ( $item->platform == 'linkedin' ) echo '💼 LinkedIn';
                                else echo esc_html( ucfirst($item->platform) ); 
                            ?>
                        </td>
                        <td><?php echo $badge; ?></td>
                        <td>
                            <?php echo date( 'M j, Y - g:i A', strtotime( $item->scheduled_time ) ); ?>
                        </td>
                        <td>
                            <small style="color:#d63638;"><?php echo esc_html( $item->error_message ); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>