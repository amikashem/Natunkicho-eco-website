<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;

// ==============================================================================
// 10X UPGRADE: BULK CSV EXPORT
// ==============================================================================
if ( isset( $_POST['nk_admin_export_csv'] ) && wp_verify_nonce( $_POST['nk_admin_export_csv_nonce'], 'nk_admin_export' ) ) {
    // Clean output buffer to ensure pure CSV download
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nk-subscribers-' . date('Y-m-d') . '.csv"');
    
    $out = fopen('php://output', 'w');
    fputcsv($out, array('Name', 'Email', 'Status', 'Source', 'Interests', 'Joined Date'));
    
    // Fetch all users without pagination limit
    $all_subs = NK_Subscriber_Manager::get_all('', 1, 999999);
    if ( $all_subs ) {
        foreach ( $all_subs as $s ) {
            fputcsv($out, array($s['name'], $s['email'], $s['status'], $s['source'], $s['interests'], $s['created_at']));
        }
    }
    fclose($out);
    exit;
}

// ==============================================================================
// 10X UPGRADE: BULK CSV IMPORT
// ==============================================================================
if ( isset( $_POST['nk_admin_import_csv'] ) && wp_verify_nonce( $_POST['nk_admin_import_csv_nonce'], 'nk_admin_import' ) ) {
    if ( ! empty($_FILES['csv_file']['tmp_name']) ) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        $import_count = 0;
        
        // Skip header row
        fgetcsv($file);
        
        while ( ($row = fgetcsv($file)) !== FALSE ) {
            // Assume Column 1 is Name, Column 2 is Email (Fallback to Col 1 for email if missing)
            $name = isset($row[0]) ? sanitize_text_field($row[0]) : '';
            $email = isset($row[1]) ? sanitize_email($row[1]) : sanitize_email($row[0]);
            
            if ( is_email($email) ) {
                NK_Subscriber_Manager::subscribe($email, $name, 'imported', 'csv_import', false);
                $import_count++;
            }
        }
        fclose($file);
        echo '<div class="notice notice-success is-dismissible" style="border-left-color: #10b981;"><p>✅ <strong>CSV Import Complete!</strong> Successfully added/updated <strong>' . esc_html($import_count) . '</strong> subscribers.</p></div>';
    }
}

// ==============================================================================
// FORCE DATABASE SYNC
// ==============================================================================
if ( isset( $_POST['nk_admin_force_sync_nonce'] ) && wp_verify_nonce( $_POST['nk_admin_force_sync_nonce'], 'nk_admin_force_sync' ) ) {
    $users = get_users(array('fields' => array('ID', 'user_email', 'display_name')));
    $sync_count = 0;
    if ( class_exists('NK_Subscriber_Manager') && method_exists('NK_Subscriber_Manager', 'sync_wp_user') ) {
        foreach ( $users as $u ) { NK_Subscriber_Manager::sync_wp_user($u->ID); $sync_count++; }
        echo '<div class="notice notice-success is-dismissible"><p>🎉 <strong>Ecosystem Sync Complete!</strong> Populated <strong>' . esc_html($sync_count) . '</strong> active profiles.</p></div>';
    }
}

// ==============================================================================
// INSTANT DIRECT EMAIL
// ==============================================================================
if ( isset( $_POST['nk_admin_direct_email_nonce'] ) && wp_verify_nonce( $_POST['nk_admin_direct_email_nonce'], 'nk_admin_direct_email' ) ) {
    $to = sanitize_email( $_POST['direct_to'] );
    $subject = sanitize_text_field( $_POST['direct_subject'] );
    $body = wp_kses_post( $_POST['direct_body'] );
    
    if ( class_exists('NK_Provider_Manager') ) {
        $provider = NK_Provider_Manager::get_active_provider();
        if ( $provider && $provider->is_configured() ) {
            $result = $provider->send( $to, '', $subject, $body );
            if ( $result['success'] ) {
                echo '<div class="notice notice-success is-dismissible"><p>🚀 <strong>SUCCESS:</strong> Email dispatched INSTANTLY via ' . esc_html(strtoupper($provider->get_name())) . ' to ' . esc_html($to) . '.</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>❌ <strong>API ERROR:</strong> ' . esc_html($result['error']) . '</p></div>';
            }
        }
    }
}

// Handle Manual Add Subscriber
if ( isset( $_POST['nk_admin_add_subscriber_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nk_admin_add_subscriber_nonce'] ) ), 'nk_admin_add_subscriber' ) ) {
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    NK_Subscriber_Manager::subscribe( $email, $name, '', 'admin_manual', false );
    echo '<div class="notice notice-success is-dismissible"><p>Subscriber added successfully.</p></div>';
}

$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
$page          = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
$subscribers   = NK_Subscriber_Manager::get_all( $status_filter, $page, 50 );
?>
<div class="wrap">
    <h1 style="font-weight: 800; border-left: 4px solid #0A66C2; padding-left: 15px; margin-bottom: 25px;">Subscribers & Master CRM Database</h1>

    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom: 30px; max-width: 1000px;">
        
        <div style="flex:1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 4px solid #0A66C2;">
            <h2 style="margin-top: 0; padding:0; font-size:16px;">📥 Bulk Import via CSV</h2>
            <p style="font-size:12px; color:#64748b;">Upload a CSV file. Column 1 must be <strong>Name</strong>, Column 2 must be <strong>Email</strong>.</p>
            <form method="post" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center;">
                <?php wp_nonce_field( 'nk_admin_import', 'nk_admin_import_csv_nonce' ); ?>
                <input type="file" name="csv_file" accept=".csv" required style="font-size:13px;">
                <button type="submit" name="nk_admin_import_csv" class="button button-primary" style="background:#0A66C2; border-color:#0A66C2;">Upload & Import</button>
            </form>
        </div>

        <div style="flex:1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 4px solid #10b981;">
            <h2 style="margin-top: 0; padding:0; font-size:16px;">📤 Bulk Export Database</h2>
            <p style="font-size:12px; color:#64748b;">Download your entire mailing list, including suppression status and joined dates, as a secure CSV file.</p>
            <form method="post">
                <?php wp_nonce_field( 'nk_admin_export', 'nk_admin_export_csv_nonce' ); ?>
                <button type="submit" name="nk_admin_export_csv" class="button button-primary" style="background:#10b981; border-color:#059669;">Download CSV File</button>
            </form>
        </div>
    </div>

    <div style="background: #fffbeb; border: 1px solid #f59e0b; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin-bottom: 25px; max-width: 1000px; display: flex; align-items: center; justify-content: space-between;">
        <div style="max-width: 70%;">
            <h4 style="margin:0 0 5px 0; color:#92400e; font-size:15px;">🔄 Sync WordPress Profiles</h4>
            <p style="margin:0; font-size:13px; color:#b45309;">Index all registered WordPress users into the mailing platform.</p>
        </div>
        <form method="post">
            <?php wp_nonce_field( 'nk_admin_force_sync', 'nk_admin_force_sync_nonce' ); ?>
            <button type="submit" class="button button-primary" style="background:#f59e0b; border-color:#d97706; text-shadow:none;">Synchronize Now</button>
        </form>
    </div>

    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom: 30px; max-width: 1000px;">
        <div style="flex:2; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; padding:0; font-size:16px;">⚡ Send Instant Direct Mail</h2>
            <form method="post">
                <?php wp_nonce_field( 'nk_admin_direct_email', 'nk_admin_direct_email_nonce' ); ?>
                <div style="display:flex; gap:10px; margin-bottom:10px;">
                    <input type="email" name="direct_to" required placeholder="Recipient Email" style="flex:1;">
                    <input type="text" name="direct_subject" required placeholder="Subject Line" style="flex:1;">
                </div>
                <?php wp_editor('', 'direct_body', ['textarea_rows' => 4, 'media_buttons' => false]); ?>
                <button type="submit" class="button button-primary" style="background:#0f172a; border-color:#0f172a; margin-top:10px;">Send Instantly</button>
            </form>
        </div>

        <div style="flex:1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; padding:0; font-size:16px;">Add Single Subscriber</h2>
            <form method="post" style="display:flex; flex-direction:column; gap: 10px; margin-top:10px;">
                <?php wp_nonce_field( 'nk_admin_add_subscriber', 'nk_admin_add_subscriber_nonce' ); ?>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <button class="button button-secondary" type="submit">Add User</button>
            </form>
        </div>
    </div>

    <ul class="subsubsub" style="margin-bottom: 15px; font-size:14px;">
        <li><a href="?page=nk-email-subscribers" style="font-weight:bold;">All</a> |</li>
        <li><a href="?page=nk-email-subscribers&status=active">Active</a> |</li>
        <li><a href="?page=nk-email-subscribers&status=pending">Pending</a> |</li>
        <li><a href="?page=nk-email-subscribers&status=unsubscribed">Unsubscribed</a> |</li>
        <li><a href="?page=nk-email-subscribers&status=bounced">Bounced</a></li>
    </ul>

    <table class="widefat striped" style="border-radius:8px; overflow:hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="padding:15px;">Name</th><th style="padding:15px;">Email</th><th style="padding:15px;">Status</th><th style="padding:15px;">Source</th><th style="padding:15px;">Interests</th><th style="padding:15px;">Joined</th>
            </tr>
        </thead>
        <tbody>
        <?php if ( $subscribers ) : foreach ( $subscribers as $s ) : ?>
            <tr>
                <td style="padding:12px;"><strong><?php echo esc_html( $s['name'] ); ?></strong></td>
                <td style="padding:12px;"><a href="mailto:<?php echo esc_attr( $s['email'] ); ?>"><?php echo esc_html( $s['email'] ); ?></a></td>
                <td style="padding:12px;">
                    <?php 
                        $color = '#10b981'; if($s['status'] === 'unsubscribed') $color = '#f59e0b'; if(in_array($s['status'], ['suppressed', 'bounced', 'failed'])) $color = '#ef4444';
                    ?>
                    <span style="color:<?php echo $color; ?>; font-weight:bold; text-transform:uppercase; font-size:11px; padding:3px 8px; border-radius:12px; background:rgba(0,0,0,0.05);"><?php echo esc_html( $s['status'] ); ?></span>
                </td>
                <td style="padding:12px; color:#64748b;"><?php echo esc_html( $s['source'] ); ?></td>
                <td style="padding:12px;"><span style="background:#e2e8f0; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold; text-transform:uppercase;"><?php echo esc_html( $s['interests'] ); ?></span></td>
                <td style="padding:12px; color:#64748b;"><?php echo esc_html( date('M j, Y', strtotime($s['created_at'])) ); ?></td>
            </tr>
        <?php endforeach; else : ?>
            <tr><td colspan="6" style="padding:30px; text-align:center;">No subscribers found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>