<?php if (!defined('ABSPATH')) exit; ?>

<div class="nkrp-search-header-text" style="text-align: center; margin-bottom: 25px;">
    <?php if ($search_type === 'candidates'): ?>
        <h2 style="margin: 0 0 10px 0; font-size: 28px; color: #0f172a; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">Discover Hospitality Talent</h2>
        <p style="margin: 0; color: #64748b; font-size: 16px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">Search professionals by role, specialized skills, and location.</p>
    <?php else: ?>
        <h2 style="margin: 0 0 10px 0; font-size: 28px; color: #0f172a; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">Find Your Next Opportunity</h2>
        <p style="margin: 0; color: #64748b; font-size: 16px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">Browse open hospitality roles and top companies.</p>
    <?php endif; ?>
</div>

<div class="nkrp-search-box">
    <form method="GET" action="" class="nkrp-form-grid">
        
        <?php if ($search_type === 'candidates'): ?>
            <div class="nkrp-input-group">
                <span class="dashicons dashicons-businessman"></span>
                <input type="text" name="role" value="<?= esc_attr($_GET['role'] ?? '') ?>" placeholder="<?php esc_attr_e('Role (e.g. Executive Chef)', 'nk-recruitment'); ?>">
            </div>
            <div class="nkrp-input-group">
                <span class="dashicons dashicons-star-filled"></span>
                <input type="text" name="skill" value="<?= esc_attr($_GET['skill'] ?? '') ?>" placeholder="<?php esc_attr_e('Skill (e.g. Fine Dining)', 'nk-recruitment'); ?>">
            </div>
            <div class="nkrp-input-group">
                <span class="dashicons dashicons-location"></span>
                <input type="text" name="location" value="<?= esc_attr($_GET['location'] ?? '') ?>" placeholder="<?php esc_attr_e('City or Region', 'nk-recruitment'); ?>">
            </div>

        <?php else: ?>
            <div class="nkrp-input-group">
                <span class="dashicons dashicons-search"></span>
                <input type="text" name="keyword" value="<?= esc_attr($_GET['keyword'] ?? '') ?>" placeholder="<?php esc_attr_e('Job title, keyword, or company...', 'nk-recruitment'); ?>">
            </div>
            <div class="nkrp-input-group">
                <span class="dashicons dashicons-location"></span>
                <select name="location">
                    <option value=""><?php esc_html_e('All Locations', 'nk-recruitment'); ?></option>
                    <?php 
                    // Fallback to empty array if $locations is not set by controller
                    $locations = $locations ?? []; 
                    foreach ($locations as $loc): 
                    ?>
                        <option value="<?= esc_attr($loc) ?>" <?= selected($_GET['location'] ?? '', $loc, false) ?>><?= esc_html($loc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="nkrp-input-group">
                <span class="dashicons dashicons-category"></span>
                <select name="category">
                    <option value=""><?php esc_html_e('All Categories', 'nk-recruitment'); ?></option>
                    <?php 
                    $categories = $categories ?? [];
                    foreach ($categories as $cat): 
                    ?>
                        <option value="<?= esc_attr($cat) ?>" <?= selected($_GET['category'] ?? '', $cat, false) ?>><?= esc_html($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <button type="submit" class="nkrp-btn-search">
            <span class="dashicons dashicons-search" style="margin-right: 5px;"></span> <?php esc_html_e('Search', 'nk-recruitment'); ?>
        </button>

    </form>
</div>

<style>
    .nkrp-search-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 1100px; margin: 0 auto; }
    .nkrp-search-box { background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; border: 1px solid #f1f5f9; }
    .nkrp-form-grid { display: flex; gap: 10px; align-items: center; }
    .nkrp-input-group { position: relative; flex: 1; display: flex; align-items: center; border-right: 1px solid #e2e8f0; padding-right: 10px; }
    .nkrp-input-group:last-of-type { border-right: none; }
    
    .nkrp-input-group .dashicons { color: #94a3b8; position: absolute; left: 10px; font-size: 18px; top: 50%; transform: translateY(-50%); }
    .nkrp-input-group input, .nkrp-input-group select { width: 100%; border: none !important; padding: 12px 10px 12px 35px !important; font-size: 15px !important; color: #334155 !important; background: transparent !important; outline: none !important; box-shadow: none !important; appearance: none !important; -webkit-appearance: none !important; box-sizing: border-box !important; height: auto !important; line-height: 1.4 !important; margin: 0 !important; }
    .nkrp-input-group input:focus, .nkrp-input-group select:focus { box-shadow: none !important; outline: none !important; border: none !important; }
    
    .nkrp-btn-search { background: #2563eb !important; color: #fff !important; border: none !important; padding: 12px 30px !important; font-size: 15px !important; font-weight: 600 !important; border-radius: 8px !important; cursor: pointer !important; transition: background 0.2s !important; white-space: nowrap !important; height: auto !important; line-height: 1.4 !important; margin: 0 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; }
    .nkrp-btn-search:hover { background: #1d4ed8 !important; }
    
    @media(max-width: 768px) {
        .nkrp-form-grid { flex-direction: column; }
        .nkrp-input-group { border-right: none; border-bottom: 1px solid #e2e8f0; width: 100%; padding: 5px 0; }
        .nkrp-btn-search { width: 100%; margin-top: 10px; }
    }
</style>