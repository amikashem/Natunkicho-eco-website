<?php if (!defined('ABSPATH')) exit; 
// Variables available: $user, $employer_companies

$base_url = home_url('/employer-dashboard/');

// Handle Delete Company Logic Safely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nkrp_delete_company'])) {
    if (wp_verify_nonce($_POST['nkrp_delete_company_nonce_' . $_POST['company_id']], 'delete_company_action')) {
        global $wpdb;
        $company_table = $wpdb->prefix . 'nkrp_companies';
        $target_id = (int)$_POST['company_id'];
        
        $wpdb->delete($company_table, ['id' => $target_id, 'user_id' => $user->ID], ['%d', '%d']);
        $redirect = add_query_arg(['tab' => 'companies', 'msg' => 'deleted'], $base_url);
        echo "<script>window.location.href='" . esc_url_raw($redirect) . "';</script>";
        exit;
    }
}

?>

<div class="nkrp-dashboard-header">
    <h2>My Companies</h2>
    <a href="<?= esc_url(add_query_arg('tab', 'add-company', $base_url)) ?>" class="nkrp-btn-primary">
        <span class="dashicons dashicons-plus-alt2"></span> Add New Company
    </a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="nkrp-alert nkrp-alert-success"><span class="dashicons dashicons-yes-alt"></span> Company successfully deleted.</div>
<?php endif; ?>

<?php if (empty($employer_companies)): ?>
    <div class="nkrp-empty-state">
        <span class="dashicons dashicons-building"></span>
        <p>You haven't added any companies yet.</p>
        <p style="font-size:13px; margin-top:5px;">Create a company profile so candidates know exactly who they are applying to.</p>
        <a href="<?= esc_url(add_query_arg('tab', 'add-company', $base_url)) ?>" class="nkrp-btn-primary" style="margin-top:15px; display:inline-block; text-decoration:none;">Add Company</a>
    </div>
<?php else: ?>
    <div class="nkrp-companies-grid">
        <?php foreach ($employer_companies as $comp): 
            $logo_url = !empty($comp->logo) ? esc_url($comp->logo) : '';
            // If they uploaded using WP Media attachment ID instead of raw URL
            if (is_numeric($comp->logo)) {
                $logo_url = wp_get_attachment_image_url($comp->logo, 'medium');
            }
        ?>
            <div class="nkrp-company-card">
                <div class="nkrp-company-card-header">
                    <div class="nkrp-company-logo">
                        <?php if ($logo_url): ?>
                            <img src="<?= $logo_url ?>" alt="<?= esc_attr($comp->company_name) ?>">
                        <?php else: ?>
                            <span class="dashicons dashicons-building"></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($comp->verified == 1): ?>
                        <span class="nkrp-verified-badge" title="Verified Employer"><span class="dashicons dashicons-yes"></span></span>
                    <?php endif; ?>
                </div>
                
                <div class="nkrp-company-card-body">
                    <h3><?= esc_html($comp->company_name) ?></h3>
                    <p class="nkrp-industry"><span class="dashicons dashicons-tag"></span> <?= esc_html($comp->industry ?: 'Unspecified Industry') ?></p>
                    
                    <div class="nkrp-company-meta">
                        <?php if (!empty($comp->city) || !empty($comp->country)): ?>
                            <span title="Location"><span class="dashicons dashicons-location"></span> <?= esc_html(trim($comp->city . ', ' . $comp->country, ', ')) ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($comp->company_size)): ?>
                            <span title="Team Size"><span class="dashicons dashicons-groups"></span> <?= esc_html($comp->company_size) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="nkrp-company-card-footer">
                    <a href="<?= esc_url(add_query_arg(['tab' => 'edit-company', 'id' => $comp->id], $base_url)) ?>" class="nkrp-btn-edit">
                        <span class="dashicons dashicons-edit"></span> Edit Profile
                    </a>
                    
                    <form method="POST" action="" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this company? Jobs attached to this company will lose their branding.');">
                        <?php wp_nonce_field('delete_company_action', 'nkrp_delete_company_nonce_' . $comp->id); ?>
                        <input type="hidden" name="company_id" value="<?= esc_attr((string)$comp->id) ?>">
                        <button type="submit" name="nkrp_delete_company" class="nkrp-btn-delete" title="Delete Company">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
    .nkrp-companies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .nkrp-company-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; }
    .nkrp-company-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    
    .nkrp-company-card-header { height: 80px; background: #f1f5f9; position: relative; border-bottom: 1px solid #e2e8f0; }
    .nkrp-company-logo { width: 64px; height: 64px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; position: absolute; bottom: -32px; left: 20px; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .nkrp-company-logo img { width: 100%; height: 100%; object-fit: cover; }
    .nkrp-company-logo .dashicons { font-size: 32px; width: 32px; height: 32px; color: #94a3b8; }
    
    .nkrp-verified-badge { position: absolute; top: 10px; right: 10px; background: #10b981; color: #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
    .nkrp-verified-badge .dashicons { font-size: 14px; width: 14px; height: 14px; }

    .nkrp-company-card-body { padding: 40px 20px 20px; flex-grow: 1; }
    .nkrp-company-card-body h3 { margin: 0 0 5px 0; font-size: 18px; color: #0f172a; }
    .nkrp-industry { margin: 0 0 15px 0; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 5px; }
    .nkrp-industry .dashicons { font-size: 14px; width: 14px; height: 14px; }
    
    .nkrp-company-meta { display: flex; flex-wrap: wrap; gap: 10px; font-size: 12px; color: #475569; }
    .nkrp-company-meta span { display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; }
    .nkrp-company-meta .dashicons { font-size: 12px; width: 12px; height: 12px; color: #94a3b8; }

    .nkrp-company-card-footer { padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; }
    .nkrp-btn-edit { flex-grow: 1; text-align: center; background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 8px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; justify-content: center; align-items: center; gap: 5px; transition: 0.2s; font-size: 13px; }
    .nkrp-btn-edit:hover { background: #f1f5f9; border-color: #94a3b8; }
    
    .nkrp-btn-delete { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; }
    .nkrp-btn-delete:hover { background: #fee2e2; border-color: #fca5a5; }
</style>