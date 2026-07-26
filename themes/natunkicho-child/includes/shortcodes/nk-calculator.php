<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Universal Calculator Shortcode
 * Usage: [nk_calculator type="meat-thawing"]
 */

function nk_calculator_shortcode( $atts ) {

    $atts = shortcode_atts(
        array(
            'type' => 'meat-thawing',
        ),
        $atts
    );

    ob_start();
    ?>
    <div class="nk-calculator" data-type="<?php echo esc_attr( $atts['type'] ); ?>">
        
        <h3 class="nk-calculator-title">
Safe Frozen Meat Thawing Time Calculator
</h3>

<p class="nk-calculator-intro">
Designed for chefs, kitchen managers, and culinary students to estimate safe thawing time based on professional food safety standards.
</p>


        <div class="nk-field">
            <label>Type of Meat</label>
<small>Select the main protein you are thawing</small>

            <select id="nk-meat-type">
                <option value="chicken">Chicken / Poultry</option>
                <option value="beef">Beef</option>
                <option value="lamb">Lamb</option>
                <option value="fish">Fish / Seafood</option>
                <option value="minced">Minced Meat</option>
            </select>
        </div>

        <div class="nk-field">
            <label>Total Weight (kg)</label>
<small>Combined weight of frozen meat</small>

            <input type="number" id="nk-weight" placeholder="e.g. 2.5" step="0.1" min="0">
        </div>

        <div class="nk-field">
            <label>Thawing Method</label>
<small>Choose according to kitchen safety practice</small>

            <select id="nk-method">
                <option value="fridge">Refrigerator (0–5°C)</option>
                <option value="water">Cold Running Water</option>
                <option value="microwave">Microwave (Immediate Cooking)</option>
            </select>
        </div>

        <button type="button" class="nk-btn">Calculate Thawing Time
</button>


        <div class="nk-result"></div>

         <p class="nk-disclaimer">
        ⚠️ Professional Reminder: Thawing times are estimates only. Always follow HACCP guidelines, local food safety laws, and your kitchen SOPs.
        </p>


    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'nk_calculator', 'nk_calculator_shortcode' );
