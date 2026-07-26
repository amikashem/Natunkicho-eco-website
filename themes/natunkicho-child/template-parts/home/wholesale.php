<?php
/**
 * Template Part: Wholesale & B2B Buyer Logged-In View
 */
$current_user = wp_get_current_user();
$company_name = get_user_meta( $current_user->ID, '_billing_company', true );
$display_name = $company_name ? $company_name : $current_user->display_name;

// Placeholder metrics for future WooCommerce B2B integration
$active_orders = 0;
$lifetime_spend = "$0.00";
?>

<style>
    .nk-b2b-dashboard { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 60px 20px; min-height: 80vh; }
    .nk-dash-container { max-width: 1200px; margin: 0 auto; width: 100%; }
    
    .nk-b2b-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; padding: 40px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 40px; border-bottom: 4px solid #f59e0b; }
    .nk-b2b-header h1 { margin: 0 0 10px 0; font-size: 32px; font-weight: 800; color: #fff; line-height: 1.2; }
    .nk-b2b-header p { margin: 0; color: #cbd5e1; font-size: 16px; }
    .nk-btn-b2b { background: #f59e0b; color: #fff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; transition: 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .nk-btn-b2b:hover { background: #d97706; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(217,119,6,0.3); color:#fff; }

    .nk-metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-metric-card { background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 5px solid #f59e0b; }
    .nk-metric-card h4 { margin: 0 0 10px 0; color: #64748b; font-size: 14px; text-transform: uppercase; font-weight: 700; }
    .nk-metric-card .number { font-size: 40px; font-weight: 900; color: #0f172a; margin: 0; line-height: 1; }
    
    .nk-section-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
    .nk-b2b-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-b2b-tool { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; display: flex; align-items: flex-start; gap: 20px; text-decoration: none; transition: 0.3s; }
    .nk-b2b-tool:hover { border-color: #f59e0b; box-shadow: 0 10px 30px rgba(245,158,11,0.08); transform: translateY(-3px); }
    .nk-b2b-tool-icon { font-size: 32px; background: #fffbeb; color: #f59e0b; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nk-b2b-tool h3 { margin: 0 0 8px 0; color: #0f172a; font-size: 20px; font-weight: 800; }
    .nk-b2b-tool p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }
</style>

<div class="nk-b2b-dashboard">
    <div class="nk-dash-container">
        
        <div class="nk-b2b-header">
            <div>
                <h1>Wholesale Procurement 📦</h1>
                <p>Welcome to your B2B Portal, <?php echo esc_html($display_name); ?>. Manage your bulk hospitality orders.</p>
            </div>
            <div>
                <a href="<?php echo esc_url(site_url('/shop/')); ?>" class="nk-btn-b2b">Start a New Bulk Order</a>
            </div>
        </div>

        <div class="nk-metrics-grid">
            <div class="nk-metric-card" style="border-left-color: #0A66C2;">
                <h4>Active Orders Pending</h4>
                <div class="number"><?php echo intval($active_orders); ?></div>
            </div>
            <div class="nk-metric-card" style="border-left-color: #10b981;">
                <h4>Current Pricing Tier</h4>
                <div class="number" style="font-size: 28px; margin-top: 10px;">Gold (15% Off)</div>
            </div>
            <div class="nk-metric-card" style="border-left-color: #f59e0b;">
                <h4>Lifetime Spend</h4>
                <div class="number"><?php echo esc_html($lifetime_spend); ?></div>
            </div>
        </div>

        <h2 class="nk-section-title">B2B Tools & Resources</h2>
        <div class="nk-b2b-actions">
            <a href="<?php echo esc_url(site_url('/my-account/orders/')); ?>" class="nk-b2b-tool">
                <div class="nk-b2b-tool-icon">📋</div>
                <div>
                    <h3>Order History</h3>
                    <p>Track your current shipments, download past invoices, and one-click reorder supplies.</p>
                </div>
            </a>
            
            <a href="<?php echo esc_url(site_url('/bulk-order-form/')); ?>" class="nk-b2b-tool">
                <div class="nk-b2b-tool-icon" style="color: #10b981; background: #ecfdf5;">🛒</div>
                <div>
                    <h3>Quick Bulk Order</h3>
                    <p>Know what you need? Use our rapid SKU entry form to place massive orders instantly.</p>
                </div>
            </a>
            
            <a href="<?php echo esc_url(site_url('/contact/')); ?>" class="nk-b2b-tool">
                <div class="nk-b2b-tool-icon" style="color: #8b5cf6; background: #f5f3ff;">💬</div>
                <div>
                    <h3>Request Custom Quote</h3>
                    <p>Opening a new hotel or restaurant? Request a custom quote for bulk equipment.</p>
                </div>
            </a>
        </div>

    </div>
</div>