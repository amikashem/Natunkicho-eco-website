<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================================================
 * SaaS PRICING TABLES (Smart Dual-Context & Multi-Tier)
 * Path: includes/shortcodes/nk-pricing-tables.php
 * Shortcode format:
 * [nk_pricing c_3m="2975" c_6m="2976" c_12m="2977" c_life="2978" e_3m="2979" e_6m="2980" e_12m="2981" e_life="2982"]
 * =========================================================================
 */
function nk_pricing_tables_shortcode( $atts ) {
    // Extract 8 specific WooCommerce Product IDs from the shortcode
    $atts = shortcode_atts( [
        'c_3m' => '', 'c_6m' => '', 'c_12m' => '', 'c_life' => '',
        'e_3m' => '', 'e_6m' => '', 'e_12m' => '', 'e_life' => ''
    ], $atts );

    $user_id = get_current_user_id();
    $active_view = function_exists('nk_get_active_workspace') ? nk_get_active_workspace($user_id) : 'candidate';
    $is_premium = function_exists('nk_is_user_premium') ? nk_is_user_premium($user_id) : false;

    // Define the 4 premium packages dynamically based on user role
    if ( $active_view === 'employer' ) {
        $packages = [
            ['id' => $atts['e_3m'],   'title' => '3 Months',  'desc' => 'Short-term hiring & full CV access.'],
            ['id' => $atts['e_6m'],   'title' => '6 Months',  'desc' => 'Great for growing hospitality teams.'],
            ['id' => $atts['e_12m'],  'title' => '12 Months', 'desc' => 'Year-round active recruiting access.'],
            ['id' => $atts['e_life'], 'title' => 'Lifetime',  'desc' => 'Pay once. Recruit top talent forever.']
        ];
    } else {
        $packages = [
            ['id' => $atts['c_3m'],   'title' => '3 Months',  'desc' => 'Profile boost for the current season.'],
            ['id' => $atts['c_6m'],   'title' => '6 Months',  'desc' => 'Ideal for active & serious job seekers.'],
            ['id' => $atts['c_12m'],  'title' => '12 Months', 'desc' => 'Year-round career growth & alerts.'],
            ['id' => $atts['c_life'], 'title' => 'Lifetime',  'desc' => 'Lifetime access to all premium tools.']
        ];
    }

    ob_start();
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
        
        /* WooCommerce Dynamic Price formatting */
        .nk-pricing-price .woocommerce-Price-amount { font-size: 32px !important; }
        .nk-pricing-price .woocommerce-Price-currencySymbol { font-size: 20px !important; vertical-align: super; margin-right: 2px; color: #64748b; }
    </style>

    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="font-size: 32px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Upgrade Your Hospitality Career</h2>
        <p style="color: #64748b; font-size: 16px; max-width: 600px; margin: 0 auto;">
            Choose the plan that fits your needs. Viewing packages for <strong><?php echo $active_view === 'employer' ? 'Employers & Recruiters' : 'Candidates & Job Seekers'; ?></strong>.
        </p>
    </div>

    <div class="nk-pricing-grid">
        
        <div class="nk-pricing-card free">
            <h3 style="margin:0; font-size:18px; color:#475569;">Free Basic</h3>
            <div class="nk-pricing-price">Free</div>
            <p style="color:#64748b; font-size:13px; margin: 0 0 15px 0;">Essential platform access.</p>
            
            <ul style="list-style:none; padding:0; margin:0; text-align:left; font-size:13px; color:#334155; margin-bottom: 20px;">
                <li style="margin-bottom:8px;">✔️ Basic Dashboard Access</li>
                <li style="margin-bottom:8px;">✔️ Blurred Talent View</li>
                <li style="margin-bottom:8px; color:#94a3b8;">❌ No Instant Messaging</li>
                <li style="color:#94a3b8;">❌ No CV Downloads</li>
            </ul>

            <div class="nk-pricing-actions">
                <?php if ( !$is_premium ) : ?>
                    <a href="#" class="nk-btn-outline" style="cursor: default; padding: 12px; font-size: 14px;">Your Current Plan</a>
                <?php endif; ?>
            </div>
        </div>

        <?php foreach ( $packages as $pkg ) : 
            if ( empty($pkg['id']) ) continue; // Skip if no product ID provided
            
            // Auto-fetch price from WooCommerce
            $product = function_exists('wc_get_product') ? wc_get_product($pkg['id']) : false;
            $price_html = $product ? $product->get_price_html() : 'Premium';
        ?>
            <div class="nk-pricing-card premium">
                <?php if($pkg['title'] === '12 Months'): ?>
                    <div class="nk-badge">Best Value</div>
                <?php endif; ?>
                
                <h3 style="margin:0; font-size:18px; color:#0A66C2;"><?php echo esc_html($pkg['title']); ?></h3>
                
                <div class="nk-pricing-price">
                    <?php echo $price_html; ?>
                </div>
                
                <p style="color:#64748b; font-size:13px; margin: 0 0 15px 0;">
                    <?php echo esc_html($pkg['desc']); ?>
                </p>

                <ul style="list-style:none; padding:0; margin:0; text-align:left; font-size:13px; color:#334155; margin-bottom: 20px;">
                    <li style="margin-bottom:8px;">✔️ <strong style="color:#10b981;">Full Premium Access</strong></li>
                    <li style="margin-bottom:8px;">✔️ Instant CV Downloads</li>
                    <li style="margin-bottom:8px;">✔️ Direct Messaging</li>
                    <li>✔️ Unrestricted Profiles</li>
                </ul>

                <div class="nk-pricing-actions">
                    <?php if ( $is_premium ) : ?>
                        <a href="/dashboard/" class="nk-btn-solid" style="background:#10b981;">Go to Dashboard</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(site_url('/checkout/?add-to-cart=' . $pkg['id'])); ?>" class="nk-btn-solid">Upgrade to Premium</a>
                        
                        <a href="<?php echo esc_url(get_permalink($pkg['id'])); ?>" class="nk-btn-outline">Details Below &rsaquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_pricing', 'nk_pricing_tables_shortcode' );