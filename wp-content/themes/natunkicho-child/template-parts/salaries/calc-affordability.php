<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// 10X FIX: Pass the Salary's $currency directly into the Cost of Living AI to guarantee matching math!
if (function_exists('nk_get_or_estimate_cost_of_living')) {
    $col_data = nk_get_or_estimate_cost_of_living($display_country, $currency);
} else {
    $table_col = $wpdb->prefix . 'nk_cost_of_living';
    $col_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_col WHERE country = %s LIMIT 1", $display_country));
}

$rent = $col_data ? floatval($col_data->rent_est) : 0;
$food = $col_data ? floatval($col_data->food_est) : 0;
$transport = $col_data ? floatval($col_data->transport_est) : 0;
$col_currency = $col_data ? $col_data->currency : $currency;
$total_expenses = $rent + $food + $transport;

// Remove commas from avg_salary so JS can calculate it properly
$clean_avg = str_replace(',', '', $avg_salary);
?>

<style>
    .nk-calc-wrapper { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .nk-calc-left h3 { margin: 0 0 10px 0; font-size: 24px; font-weight: 800; color: #0f172a; }
    .nk-calc-left p { color: #64748b; font-size: 15px; margin-bottom: 30px; }
    .nk-calc-input-group { margin-bottom: 20px; }
    .nk-calc-input-group label { display: block; font-size: 14px; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .nk-calc-input-group input { width: 100%; height: 55px; padding: 0 20px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 18px; font-weight: bold; color: #0f172a; outline: none; transition: 0.2s; box-sizing: border-box; }
    .nk-calc-input-group input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
    
    .nk-calc-right { background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; }
    .nk-expense-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #cbd5e1; color: #475569; font-size: 15px; font-weight: 600; }
    .nk-expense-row:last-child { border-bottom: none; }
    .nk-calc-total { background: #10b981; color: #fff; padding: 25px; border-radius: 12px; text-align: center; margin-top: 20px; box-shadow: 0 10px 20px rgba(16,185,129,0.2); }
    .nk-calc-total h4 { margin: 0 0 5px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #ecfdf5; }
    .nk-calc-total .amount { font-size: 36px; font-weight: 900; line-height: 1; }

    @media (max-width: 768px) {
        .nk-calc-wrapper { grid-template-columns: 1fr; gap: 20px; padding: 20px; }
    }
</style>

<div class="nk-calc-wrapper" id="affordability-calc">
    <div class="nk-calc-left">
        <h3>"Can I Afford It?" Calculator 🧮</h3>
        <p>Enter your offered salary to see how much you could actually save living in <strong><?php echo esc_html($display_country); ?></strong>.</p>
        
        <div class="nk-calc-input-group">
            <label>Your Offered Salary / Month (<?php echo esc_html($col_currency); ?>)</label>
            <input type="number" id="user_salary" value="<?php echo esc_attr($clean_avg); ?>" onkeyup="nkCalculateSavings()" onchange="nkCalculateSavings()">
        </div>

        <div style="background: #eff6ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0A66C2; font-size: 13px; color: #1e3a8a;">
            💡 <strong>Pro Tip:</strong> Ensure you check if your employer provides free accommodation. If they do, your savings will skyrocket!
        </div>
    </div>

    <div class="nk-calc-right">
        <h4 style="margin: 0 0 20px 0; color: #0f172a; font-size: 18px;">Estimated Monthly Expenses</h4>
        
        <div class="nk-expense-row">
            <span>🏠 Rent / Housing</span>
            <span style="color: #ef4444;">- <?php echo number_format($rent); ?></span>
        </div>
        <div class="nk-expense-row">
            <span>🍽️ Food & Groceries</span>
            <span style="color: #ef4444;">- <?php echo number_format($food); ?></span>
        </div>
        <div class="nk-expense-row">
            <span>🚌 Transportation</span>
            <span style="color: #ef4444;">- <?php echo number_format($transport); ?></span>
        </div>
        
        <div class="nk-calc-total">
            <h4>Estimated Monthly Savings</h4>
            <div class="amount" id="calc_savings_result">Calculating...</div>
        </div>
    </div>
</div>

<script>
    const expenses = <?php echo floatval($total_expenses); ?>;
    const currency = '<?php echo esc_js($col_currency); ?>';

    function nkCalculateSavings() {
        let salaryInput = document.getElementById('user_salary').value;
        let salary = parseFloat(salaryInput) || 0;
        let savings = salary - expenses;
        
        let resultDiv = document.getElementById('calc_savings_result');
        let totalDiv = resultDiv.parentElement;

        // Format number with commas
        let formattedSavings = savings.toLocaleString('en-US', { maximumFractionDigits: 0 });

        resultDiv.innerText = formattedSavings + ' ' + currency;

        if (savings <= 0) {
            totalDiv.style.background = '#ef4444'; // Red if negative
            totalDiv.style.boxShadow = '0 10px 20px rgba(239,68,68,0.2)';
            if (salary === 0) resultDiv.innerText = '0 ' + currency;
        } else {
            totalDiv.style.background = '#10b981'; // Green if positive
            totalDiv.style.boxShadow = '0 10px 20px rgba(16,185,129,0.2)';
        }
    }

    // Run on load
    document.addEventListener("DOMContentLoaded", nkCalculateSavings);
</script>