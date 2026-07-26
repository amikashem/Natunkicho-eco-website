<?php if (!defined('ABSPATH')) exit; 
global $wpdb;
$user_id = get_current_user_id();
$company_table = $wpdb->prefix . 'nkrp_companies';

// Fetch companies if not passed explicitly in scope
if (!isset($employer_companies)) {
    $employer_companies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$company_table} WHERE user_id = %d ORDER BY id DESC", $user_id));
}

// Calculate Stats dynamically
$total = is_array($employer_companies) ? count($employer_companies) : 0;
$active = 0;
$pending = 0;
$featured = 0;

if ($total > 0) {
    foreach ($employer_companies as $comp) {
        if (($comp->status ?? '') === 'active') $active++;
        if (($comp->status ?? '') === 'pending') $pending++;
        if (($comp->featured ?? 0) == 1) $featured++;
    }
}

$msg = sanitize_text_field($_GET['msg'] ?? '');
?>

<div class="nkrp-dashboard-header">
    <h2>My Companies</h2>
    <a href="<?= esc_url(add_query_arg('tab', 'add-company', home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-primary">
        <span class="dashicons dashicons-plus-alt2"></span> Add New Company
    </a>
</div>

<?php if ($msg === 'created'): ?>
    <div class="nkrp-alert nkrp-alert-success"><span class="dashicons dashicons-yes-alt"></span> Company profile created successfully!</div>
<?php elseif ($msg === 'updated'): ?>
    <div class="nkrp-alert nkrp-alert-success"><span class="dashicons dashicons-yes-alt"></span> Company profile updated successfully!</div>
<?php endif; ?>

<!-- BEAUTIFUL STATS GRID -->
<div class="nkrp-stats-grid">
    <div class="nkrp-stat-card">
        <div class="nkrp-stat-icon" style="background:#eff6ff; color:#2563eb;"><span class="dashicons dashicons-building"></span></div>
        <div class="nkrp-stat-details">
            <h3><?= $total ?></h3>
            <p>Total Companies</p>
        </div>
    </div>
    <div class="nkrp-stat-card">
        <div class="nkrp-stat-icon" style="background:#dcfce7; color:#166534;"><span class="dashicons dashicons-yes-alt"></span></div>
        <div class="nkrp-stat-details">
            <h3><?= $active ?></h3>
            <p>Active</p>
        </div>
    </div>
    <div class="nkrp-stat-card">
        <div class="nkrp-stat-icon" style="background:#fef9c3; color:#a16207;"><span class="dashicons dashicons-clock"></span></div>
        <div class="nkrp-stat-details">
            <h3><?= $pending ?></h3>
            <p>Pending</p>
        </div>
    </div>
    <div class="nkrp-stat-card">
        <div class="nkrp-stat-icon" style="background:#fef3c7; color:#b45309;"><span class="dashicons dashicons-star-filled"></span></div>
        <div class="nkrp-stat-details">
            <h3><?= $featured ?></h3>
            <p>Featured</p>
        </div>
    </div>
</div>

<!-- MODERN SEARCH & FILTER BAR -->
<div class="nkrp-filter-bar">
    <div style="flex-grow: 2; position: relative;">
        <span class="dashicons dashicons-search" style="position: absolute; left: 12px; top: 12px; color: #94a3b8;"></span>
        <input type="text" id="nkrp-company-search" placeholder="Search company by name..." class="nkrp-filter-input" style="padding-left: 40px; width: 100%;">
    </div>
    <select id="nkrp-company-status" class="nkrp-filter-select">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="pending">Pending Review</option>
        <option value="inactive">Inactive</option>
    </select>
    <button type="button" class="nkrp-btn-secondary" onclick="document.getElementById('nkrp-company-search').value=''; document.getElementById('nkrp-company-status').value='';">Reset Filter</button>
</div>

<!-- DYNAMIC COMPANY LIST -->
<div class="nkrp-companies-wrapper">
    <?php if ($total === 0): ?>
        <div style="text-align: center; padding: 50px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
            <span class="dashicons dashicons-building" style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 15px;"></span>
            <h3 style="margin: 0 0 10px 0; color: #0f172a; font-size:20px;">No companies found</h3>
            <p style="margin:0; color:#64748b;">Create your first company profile to start posting jobs to the platform.</p>
            <a href="<?= esc_url(add_query_arg('tab', 'add-company', home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-primary" style="margin-top: 20px;">
                <span class="dashicons dashicons-plus-alt2"></span> Create Company
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($employer_companies as $comp): 
            $logo_url = '';
            if (!empty($comp->logo)) {
                $logo_url = is_numeric($comp->logo) ? wp_get_attachment_image_url($comp->logo, 'thumbnail') : esc_url($comp->logo);
            }
        ?>
            <div class="nkrp-company-list-item">
                <div class="nkrp-company-info">
                    <div class="nkrp-company-logo">
                        <?php if ($logo_url): ?>
                            <img src="<?= $logo_url ?>" alt="<?= esc_attr($comp->company_name) ?>">
                        <?php else: ?>
                            <span class="dashicons dashicons-building"></span>
                        <?php endif; ?>
                    </div>
                    <div class="nkrp-company-details">
                        <h3 class="nkrp-company-title">
                            <?= esc_html($comp->company_name) ?>
                            <?php if (($comp->featured ?? 0) == 1): ?>
                                <span class="nkrp-badge-gold" title="Featured Company"><span class="dashicons dashicons-star-filled" style="font-size:12px;width:12px;height:12px;margin-top:2px;"></span></span>
                            <?php endif; ?>
                        </h3>
                        <div class="nkrp-company-meta">
                            <?php if (!empty($comp->industry)): ?>
                                <span><span class="dashicons dashicons-portfolio"></span> <?= esc_html($comp->industry) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($comp->city) || !empty($comp->country)): ?>
                                <span><span class="dashicons dashicons-location"></span> <?= esc_html(trim(($comp->city ?? '') . ', ' . ($comp->country ?? ''), ', ')) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="nkrp-company-status-badge">
                    <?php 
                    $status = $comp->status ?? 'pending';
                    if ($status === 'active') {
                        echo '<span class="nkrp-status-active"><span class="dashicons dashicons-yes-alt" style="font-size:14px; width:14px; height:14px;"></span> Active</span>';
                    } elseif ($status === 'inactive') {
                        echo '<span class="nkrp-status-inactive">Inactive</span>';
                    } else {
                        echo '<span class="nkrp-status-pending"><span class="dashicons dashicons-clock" style="font-size:14px; width:14px; height:14px;"></span> Pending</span>';
                    }
                    ?>
                </div>

                <div class="nkrp-company-actions">
                    <a href="<?= esc_url(home_url('/company-profile/?slug=' . esc_attr($comp->company_slug))) ?>" target="_blank" class="nkrp-btn-action nkrp-btn-view" title="View Public Profile">
                        <span class="dashicons dashicons-visibility"></span> View
                    </a>
                    <a href="<?= esc_url(add_query_arg(['tab' => 'edit-company', 'id' => $comp->id], home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-action nkrp-btn-edit" title="Edit Company">
                        <span class="dashicons dashicons-edit"></span> Edit
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    /* Stats Grid */
    .nkrp-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    .nkrp-stat-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .nkrp-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nkrp-stat-icon .dashicons { font-size: 24px; width: 24px; height: 24px; }
    .nkrp-stat-details h3 { margin: 0; font-size: 24px; color: #0f172a; }
    .nkrp-stat-details p { margin: 0; font-size: 14px; color: #64748b; font-weight: 500; }

    /* Filter Bar */
    .nkrp-filter-bar { display: flex; gap: 15px; background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; align-items: center; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.02);}
    .nkrp-filter-input, .nkrp-filter-select { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #334155; outline: none; transition: border 0.2s; height: 42px; box-sizing: border-box; }
    .nkrp-filter-input:focus, .nkrp-filter-select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .nkrp-filter-select { background-color: #fff; cursor: pointer; min-width: 180px; }

    /* Company List */
    .nkrp-companies-wrapper { display: flex; flex-direction: column; gap: 15px; }
    .nkrp-company-list-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease; gap: 20px; }
    .nkrp-company-list-item:hover { box-shadow: 0 6px 10px -2px rgba(0,0,0,0.05); border-color: #cbd5e1; transform: translateY(-1px); }
    
    .nkrp-company-info { display: flex; align-items: center; gap: 20px; flex-grow: 1; }
    .nkrp-company-logo { width: 64px; height: 64px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; padding: 6px; box-sizing: border-box;}
    .nkrp-company-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .nkrp-company-logo .dashicons { font-size: 32px; width: 32px; height: 32px; color: #cbd5e1; }
    
    .nkrp-company-title { margin: 0 0 6px 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; gap: 8px; font-weight: 700; }
    .nkrp-badge-gold { background: #fef3c7; color: #b45309; padding: 2px 4px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; }
    .nkrp-company-meta { display: flex; gap: 15px; font-size: 13px; color: #64748b; flex-wrap: wrap; }
    .nkrp-company-meta span { display: flex; align-items: center; gap: 4px; font-weight: 500;}
    .nkrp-company-meta .dashicons { font-size: 14px; width: 14px; height: 14px; color: #94a3b8; }

    .nkrp-company-status-badge { flex-shrink: 0; width: 110px; text-align: center; }
    .nkrp-status-active { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #bbf7d0;}
    .nkrp-status-pending { background: #fef9c3; color: #a16207; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #fde047;}
    .nkrp-status-inactive { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; border: 1px solid #e2e8f0;}

    .nkrp-company-actions { display: flex; gap: 10px; flex-shrink: 0; }
    .nkrp-btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; border: 1px solid transparent; }
    .nkrp-btn-view { background: #f8fafc; color: #475569; border-color: #cbd5e1; }
    .nkrp-btn-view:hover { background: #f1f5f9; color: #0f172a; border-color: #94a3b8; }
    .nkrp-btn-edit { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .nkrp-btn-edit:hover { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }

    /* Responsive Adjustments */
    @media(max-width: 992px) {
        .nkrp-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media(max-width: 768px) {
        .nkrp-stats-grid { grid-template-columns: 1fr; }
        .nkrp-company-list-item { flex-direction: column; align-items: flex-start; }
        .nkrp-company-status-badge { width: auto; }
        .nkrp-company-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
        .nkrp-btn-action { justify-content: center; }
    }
</style>