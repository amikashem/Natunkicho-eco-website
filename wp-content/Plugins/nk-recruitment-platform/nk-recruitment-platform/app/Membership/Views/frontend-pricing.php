<?php if (!defined('ABSPATH')) exit; 

// 1. Get the Current User's Active Subscription Details Securely
$current_user_id = get_current_user_id();
$active_sub = null;
$active_plan_id = 0;
$expiry_date = null;
$current_plan_price = 0.0;

if ($current_user_id > 0 && class_exists('\NKRecruitment\Membership\Plans\PlanManager')) {
    $active_sub = \NKRecruitment\Membership\Plans\PlanManager::getUserSubscription($current_user_id);
    
    if ($active_sub && !empty($active_sub->plan_key)) {
        $active_plan_id = (int) preg_replace('/[^0-9]/', '', (string)$active_sub->plan_key);
        
        // Safely check for date column (handles both typical naming conventions)
        $expiry_date = isset($active_sub->expires_at) ? $active_sub->expires_at : (isset($active_sub->end_date) ? $active_sub->end_date : null);
        
        // Get the price of the current plan to calculate if other plans are an "Upgrade"
        if (function_exists('wc_get_product')) {
            $cur_prod = wc_get_product($active_plan_id);
            if ($cur_prod) {
                $current_plan_price = (float) $cur_prod->get_price();
            }
        }
    }
}
?>

<style>
    .nk-pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; max-width: 1200px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .nk-pricing-card { background: #fff; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-direction: column; text-align: center; position: relative; transition: all 0.2s; }
    .nk-pricing-card.free { background: #f8fafc; }
    .nk-pricing-card.premium { border: 2px solid #0A66C2; box-shadow: 0 10px 30px rgba(10,102,194,0.08); transform: translateY(-3px); }
    .nk-pricing-price { font-size: 32px; font-weight: 800; color: #0f172a; margin: 15px 0; min-height: 45px; display: flex; align-items: center; justify-content: center;}
    .nk-pricing-actions { margin-top: auto; display: flex; flex-direction: column; gap: 10px; padding-top: 25px;}
    
    .nk-btn-solid { background: #0A66C2; color: #fff; padding: 12px; border-radius: 8px; font-weight: bold; text-decoration: none; transition: 0.2s; font-size: 15px;}
    .nk-btn-solid:hover { background: #04669b; color: #fff;}
    .nk-btn-outline { background: transparent; color: #64748b; border: 1px solid #cbd5e1; padding: 6px; border-radius: 6px; font-size: 12px; font-weight: bold; text-decoration: none; transition: 0.2s;}
    .nk-btn-outline:hover { background: #f8fafc; color: #0f172a; border-color: #94a3b8;}
    
    .nk-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #fef08a; color: #854d0e; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; border: 1px solid #fde047; white-space: nowrap;}
    
    .nk-feature-list { list-style:none; padding:0; margin:0; text-align:left; font-size:13px; color:#334155; margin-bottom: 20px; }
    .nk-feature-list li { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px; line-height: 1.4; }
    .nk-feature-list .dashicons-yes { color: #10b981; font-size: 16px; width: 16px; height: 16px; margin-top: 1px; }
    .nk-feature-list .dashicons-no-alt { color: #ef4444; font-size: 16px; width: 16px; height: 16px; margin-top: 1px; }
    .nk-disabled-text { color: #94a3b8; }
    
    /* WooCommerce Formatting */
    .nk-pricing-price .woocommerce-Price-amount { font-size: 32px !important; }
    .nk-pricing-price .woocommerce-Price-currencySymbol { font-size: 20px !important; vertical-align: super; margin-right: 2px; color: #64748b; }
    
    .nk-view-toggles a { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.2s; }
    .nk-view-toggles a.active { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .nk-view-toggles a.inactive { color: #64748b; }
</style>

<div style="text-align: center; margin-bottom: 40px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
    <h2 style="font-size: 32px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Upgrade Your Hospitality Career</h2>
    <p style="color: #64748b; font-size: 16px; max-width: 600px; margin: 0 auto;">
        Choose the plan that fits your needs. Viewing packages for <strong><?= $role === 'nkrp_employer' ? 'Employers & Recruiters' : 'Candidates & Job Seekers' ?></strong>.
    </p>

    <?php if (!is_user_logged_in() || current_user_can('manage_options')): ?>
        <div class="nk-view-toggles" style="margin-top: 20px; display: inline-flex; background: #f1f5f9; padding: 5px; border-radius: 8px;">
            <a href="?view=candidate" class="<?= $role === 'nkrp_candidate' ? 'active' : 'inactive' ?>">View as Candidate</a>
            <a href="?view=employer" class="<?= $role === 'nkrp_employer' ? 'active' : 'inactive' ?>">View as Employer</a>
        </div>
    <?php endif; ?>
</div>

<div class="nk-pricing-grid">
    
    <div class="nk-pricing-card free">
        <h3 style="margin:0; font-size:18px; color:#475569;">Free Basic</h3>
        <div class="nk-pricing-price">Free</div>
        <p style="color:#64748b; font-size:13px; margin: 0 0 15px 0;">Essential platform access.</p>
        
        <ul class="nk-feature-list">
            <?php foreach ($features as $f_name => $access): ?>
                <li class="<?= !$access[0] ? 'nk-disabled-text' : '' ?>">
                    <?php if ($access[0]): ?>
                        <span class="dashicons dashicons-yes"></span> 
                    <?php else: ?>
                        <span class="dashicons dashicons-no-alt"></span> 
                    <?php endif; ?>
                    <span><?= esc_html($f_name) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="nk-pricing-actions">
            <?php if (!$active_plan_id) : ?>
                <a href="#" class="nk-btn-outline" style="cursor: default; padding: 12px; font-size: 14px;">Your Current Plan</a>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach ($packages as $pkg) : 
        if (empty($pkg['id'])) continue; 
        
        $clean_id = (int) preg_replace('/[^0-9]/', '', (string)$pkg['id']);
        $price_html = '<span style="font-size:16px; color:#ef4444;">Loading...</span>';
        $this_price = 0.0;
        
        if (function_exists('wc_get_product') && $clean_id > 0) {
            $product = wc_get_product($clean_id);
            if ($product) {
                $price_html = $product->get_price_html();
                $this_price = (float) $product->get_price();
                
                if (empty($price_html)) {
                    if ($this_price > 0) {
                        $price_html = wc_price($this_price);
                    } else {
                        $price_html = '<span style="font-size:16px; color:#ef4444; font-weight:600;">No Price Set</span>';
                    }
                }
            } else {
                $price_html = '<span style="font-size:14px; color:#ef4444; font-weight:600;">Invalid Product ID: ' . $clean_id . '</span>';
            }
        }

        // Generate the EXACT WooCommerce Checkout URL dynamically
        $checkout_base_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');
        $direct_checkout_link = add_query_arg('add-to-cart', $clean_id, $checkout_base_url);
    ?>
        <div class="nk-pricing-card premium">
            <?php if(!empty($pkg['badge'])): ?>
                <div class="nk-badge"><?= esc_html($pkg['badge']) ?></div>
            <?php endif; ?>
            
            <h3 style="margin:0; font-size:18px; color:#0A66C2;"><?= esc_html($pkg['title']) ?></h3>
            
            <div class="nk-pricing-price"><?= $price_html ?></div>
            
            <p style="color:#64748b; font-size:13px; margin: 0 0 15px 0;"><?= esc_html($pkg['desc']) ?></p>

            <ul class="nk-feature-list">
                <?php foreach ($features as $f_name => $access): ?>
                    <li class="<?= !$access[1] ? 'nk-disabled-text' : '' ?>">
                        <?php if ($access[1]): ?>
                            <span class="dashicons dashicons-yes"></span> 
                        <?php else: ?>
                            <span class="dashicons dashicons-no-alt"></span> 
                        <?php endif; ?>
                        <span><?= esc_html($f_name) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="nk-pricing-actions">
                <?php if ($active_plan_id > 0) : ?>
                    <?php if ($clean_id === $active_plan_id) : ?>
                        <div style="background:#f0fdf4; border:1px solid #10b981; color:#065f46; padding:12px; border-radius:8px; font-size:13px;">
                            <div style="font-weight:800; margin-bottom:4px; font-size:14px;">Already Premium</div>
                            <?php 
                            // Determine if this is a Lifetime plan by date format OR title
                            if (empty($expiry_date) || strpos((string)$expiry_date, '0000') !== false || stripos($pkg['title'], 'lifetime') !== false) {
                                echo '<div>Lifetime Access Active</div>';
                            } else {
                                echo '<div>Expires on: <strong>' . date('M j, Y', strtotime($expiry_date)) . '</strong></div>';
                            }
                            ?>
                        </div>
                    <?php else : ?>
                        <?php if ($this_price > $current_plan_price) : ?>
                            <a href="<?= esc_url($direct_checkout_link) ?>" class="nk-btn-solid">Upgrade to <?= esc_html($pkg['title']) ?></a>
                        <?php else : ?>
                            <button class="nk-btn-outline" style="cursor:not-allowed;" disabled>Included in your plan</button>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else : ?>
                    <?php if (is_user_logged_in()): ?>
                        <a href="<?= esc_url($direct_checkout_link) ?>" class="nk-btn-solid">Upgrade Now</a>
                    <?php else: ?>
                        <a href="<?= esc_url(home_url('/login/?redirect_to=' . urlencode($direct_checkout_link))) ?>" class="nk-btn-solid">Log in to Upgrade</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>