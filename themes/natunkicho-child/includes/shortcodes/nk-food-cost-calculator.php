<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nk_fc_food_cost_calculator_shortcode() {
    ob_start();
    ?>

    <div class="nk-fc-calculator">

        <!-- =========================
        ROW 1: QUICK FOOD COST
        ========================== -->
        <section class="nk-fc-row nk-fc-quick">
            <h3>Quick Food Cost Calculator</h3>

            <div class="nk-fc-quick-grid">
                <div>
                    <label>Recipe Name</label>
                    <input type="text" class="nk-fc-q-recipe" placeholder="e.g. Chicken Curry">
                </div>

                <div>
                    <label>Total Ingredient Cost</label>
                    <input type="number" class="nk-fc-q-total-cost" step="0.01">
                </div>

                <div>
                    <label>Number of Portions</label>
                    <input type="number" class="nk-fc-q-portions" step="1">
                </div>

                <div>
                    <label>Selling Price (per portion)</label>
                    <input type="number" class="nk-fc-q-price" step="0.01">
                </div>

                <div class="nk-fc-q-action">
                    <button type="button" class="nk-fc-q-calc-btn">Calculate</button>
                </div>
            </div>

            <div class="nk-fc-q-result">
                <p>Cost per Portion: <strong><span class="nk-fc-q-cost-portion">--</span></strong></p>
                <p>Food Cost %: <strong><span class="nk-fc-q-food-cost">--</span></strong></p>
            </div>
        </section>

        <!-- =========================
        ROW 2: ADVANCED FOOD COST
        ========================== -->
        <section class="nk-fc-row nk-fc-advanced">
            <h3>Advanced Food Cost Calculator</h3>

            <div class="nk-fc-advanced-grid">

                <!-- COLUMN 1: RECIPE + COST SETTINGS -->
                <div class="nk-fc-col nk-fc-recipe-info">
                    <h4>Recipe & Pricing</h4>

                    <label>Recipe Name</label>
                    <input type="text" class="nk-fc-recipe-name">

                    <label>Number of Portions</label>
                    <input type="number" class="nk-fc-portions" step="1">

                    <label>Menu Price (per portion)</label>
                    <input type="number" class="nk-fc-menu-price" step="0.01">

                    <label class="nk-fc-premium">Tax (%)</label>
                    <input type="number" class="nk-fc-tax" step="0.1">

                    <label class="nk-fc-premium">Other Cost per Portion</label>
                    <input type="number" class="nk-fc-other-cost" step="0.01">

                    <div class="nk-fc-premium-grid">
                        <div>
                            <label>Wastage (%)</label>
                            <input type="number" step="0.1" class="nk-fc-wastage">
                        </div>
                        <div>
                            <label>Labor (%)</label>
                            <input type="number" step="0.1" class="nk-fc-labor">
                        </div>
                        <div>
                            <label>Overhead (%)</label>
                            <input type="number" step="0.1" class="nk-fc-overhead">
                        </div>
                    </div>
                </div>

                <!-- COLUMN 2: INGREDIENTS -->
                <div class="nk-fc-col nk-fc-ingredients">
                    <h4>Ingredients</h4>

                    <table class="nk-fc-ingredient-table">
                        <thead>
                            <tr>
                                <th>Ingredient</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Qty Used</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody class="nk-fc-ingredient-body">
                            <!-- JS inserts rows -->
                        </tbody>
                    </table>

                    <button type="button" class="nk-fc-add-ingredient">
                        + Add Ingredient
                    </button>
                </div>

                <!-- COLUMN 3: RESULTS -->
                <div class="nk-fc-col nk-fc-results">
                    <h4>Results</h4>

                    <p>Total Ingredient Cost:
                        <strong><span class="nk-fc-total-ingredient-cost">--</span></strong>
                    </p>

                    <p>Cost per Portion:
                        <strong><span class="nk-fc-cost-per-portion">--</span></strong>
                    </p>

                    <p>Food Cost %:
                        <strong><span class="nk-fc-food-cost-percent">--</span></strong>
                    </p>

                    <label class="nk-fc-premium">Target Food Cost (%)</label>
                    <input type="number" step="0.1" class="nk-fc-target-percent" placeholder="e.g. 30">

                    <p class="nk-fc-premium">
                        Suggested Menu Price:
                        <strong><span class="nk-fc-suggested-price">--</span></strong>
                    </p>
                </div>

            </div>
        </section>

        <!-- =========================
        ROW 3: NOTES
        ========================== -->
        <section class="nk-fc-row nk-fc-notes">
            <h4>Notes (Optional)</h4>
            <textarea class="nk-fc-notes-text"
                placeholder="Chef notes, wastage remarks, yield issues..."></textarea>
        </section>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode( 'nk_food_cost_calculator', 'nk_fc_food_cost_calculator_shortcode' );
