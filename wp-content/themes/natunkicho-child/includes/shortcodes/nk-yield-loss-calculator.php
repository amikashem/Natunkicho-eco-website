<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nk_yield_loss_calculator_shortcode() {
    ob_start();
    ?>

    <div class="nk-calculator nk-yield-calculator">

        <h3>Recipe Yield Loss Calculator (Raw → Cooked)</h3>
        <p class="nk-calculator-intro">
            Calculate trimming and cooking loss to understand real usable yield
            and accurate food costing.
        </p>

        <div class="nk-field">
            <label>Raw Weight (kg)</label>
            <input type="number" step="0.01" class="nk-yield-raw-weight">
            <small>Weight before trimming or cooking</small>
        </div>

        <div class="nk-field">
            <label>Cooked / Usable Weight (kg)</label>
            <input type="number" step="0.01" class="nk-yield-cooked-weight">
            <small>Weight after trimming & cooking</small>
        </div>

        <div class="nk-field nk-premium">
            <label>Total Raw Cost (optional)</label>
            <input type="number" step="0.01" class="nk-yield-raw-cost">
            <small>Used to calculate adjusted cost per kg</small>
        </div>

        <button type="button" class="nk-btn nk-yield-calc-btn">
            Calculate Yield
        </button>

        <div class="nk-result nk-yield-result">
            <p>Yield Percentage: <strong><span class="nk-yield-percent">--</span>%</strong></p>
            <p>Loss Percentage: <strong><span class="nk-yield-loss">--</span>%</strong></p>
            <p>Loss Weight: <strong><span class="nk-yield-loss-weight">--</span> kg</strong></p>
            <p class="nk-premium">
                Adjusted Cost per kg:
                <strong><span class="nk-yield-adjusted-cost">--</span></strong>
            </p>
        </div>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode( 'nk_yield_loss_calculator', 'nk_yield_loss_calculator_shortcode' );
