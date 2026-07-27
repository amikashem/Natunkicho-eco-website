document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.nk-fc-calculator').forEach(calculator => {

        /* =========================
           HELPER
        ========================== */
        const num = val => parseFloat(val) || 0;

        /* =========================
           QUICK CALCULATOR
        ========================== */
        const qTotalCost = calculator.querySelector('.nk-fc-q-total-cost');
        const qPortions = calculator.querySelector('.nk-fc-q-portions');
        const qPrice = calculator.querySelector('.nk-fc-q-price');
        const qBtn = calculator.querySelector('.nk-fc-q-calc-btn');

        const qCostPerPortionEl = calculator.querySelector('.nk-fc-q-cost-portion');
        const qFoodCostEl = calculator.querySelector('.nk-fc-q-food-cost');

        if (qBtn) {
            qBtn.addEventListener('click', () => {
                const totalCost = num(qTotalCost.value);
                const portions = num(qPortions.value);
                const price = num(qPrice.value);

                if (totalCost > 0 && portions > 0) {
                    const costPerPortion = totalCost / portions;
                    qCostPerPortionEl.textContent = costPerPortion.toFixed(2);

                    if (price > 0) {
                        const foodCostPercent = (costPerPortion / price) * 100;
                        qFoodCostEl.textContent = foodCostPercent.toFixed(2) + '%';
                    } else {
                        qFoodCostEl.textContent = '--';
                    }
                } else {
                    qCostPerPortionEl.textContent = '--';
                    qFoodCostEl.textContent = '--';
                }
            });
        }

        /* =========================
           ADVANCED CALCULATOR
        ========================== */

        const portionsInput = calculator.querySelector('.nk-fc-portions');
        const menuPriceInput = calculator.querySelector('.nk-fc-menu-price');
        const taxInput = calculator.querySelector('.nk-fc-tax');
        const otherCostInput = calculator.querySelector('.nk-fc-other-cost');

        const wastageInput = calculator.querySelector('.nk-fc-wastage');
        const laborInput = calculator.querySelector('.nk-fc-labor');
        const overheadInput = calculator.querySelector('.nk-fc-overhead');
        const targetPercentInput = calculator.querySelector('.nk-fc-target-percent');

        const ingredientBody = calculator.querySelector('.nk-fc-ingredient-body');
        const addIngredientBtn = calculator.querySelector('.nk-fc-add-ingredient');

        const totalIngredientCostEl = calculator.querySelector('.nk-fc-total-ingredient-cost');
        const costPerPortionEl = calculator.querySelector('.nk-fc-cost-per-portion');
        const foodCostPercentEl = calculator.querySelector('.nk-fc-food-cost-percent');
        const suggestedPriceEl = calculator.querySelector('.nk-fc-suggested-price');

        /* =========================
           ADD INGREDIENT ROW
        ========================== */
        function addIngredientRow() {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td><input type="text" class="nk-ing-name"></td>
                <td><input type="text" class="nk-ing-unit" placeholder="kg/g/ml"></td>
                <td><input type="number" step="0.01" class="nk-ing-unit-price"></td>
                <td><input type="number" step="0.01" class="nk-ing-qty"></td>
                <td><span class="nk-ing-cost">0.00</span></td>
            `;

            ingredientBody.appendChild(row);

            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', calculateAdvanced);
            });
        }

        if (addIngredientBtn) {
            addIngredientBtn.addEventListener('click', addIngredientRow);
            addIngredientRow(); // default one row
        }

        /* =========================
           ADVANCED CALCULATION
        ========================== */
        function calculateAdvanced() {

            let ingredientTotal = 0;

            ingredientBody.querySelectorAll('tr').forEach(row => {
                const price = num(row.querySelector('.nk-ing-unit-price').value);
                const qty = num(row.querySelector('.nk-ing-qty').value);
                const cost = price * qty;

                row.querySelector('.nk-ing-cost').textContent = cost.toFixed(2);
                ingredientTotal += cost;
            });

            totalIngredientCostEl.textContent = ingredientTotal.toFixed(2);

            const portions = num(portionsInput.value);
            if (portions <= 0) {
                costPerPortionEl.textContent = '--';
                foodCostPercentEl.textContent = '--';
                suggestedPriceEl.textContent = '--';
                return;
            }

            const baseCostPerPortion = ingredientTotal / portions;

            const wastageCost = baseCostPerPortion * (num(wastageInput?.value) / 100);
            const laborCost = baseCostPerPortion * (num(laborInput?.value) / 100);
            const overheadCost = baseCostPerPortion * (num(overheadInput?.value) / 100);

            const taxCost = baseCostPerPortion * (num(taxInput?.value) / 100);
            const otherCost = num(otherCostInput?.value);

            const finalCostPerPortion =
                baseCostPerPortion +
                wastageCost +
                laborCost +
                overheadCost +
                taxCost +
                otherCost;

            costPerPortionEl.textContent = finalCostPerPortion.toFixed(2);

            const menuPrice = num(menuPriceInput.value);
            if (menuPrice > 0) {
                const foodCostPercent = (finalCostPerPortion / menuPrice) * 100;
                foodCostPercentEl.textContent = foodCostPercent.toFixed(2) + '%';
            } else {
                foodCostPercentEl.textContent = '--';
            }

            const targetPercent = num(targetPercentInput?.value);
            if (targetPercent > 0) {
                const suggestedPrice = finalCostPerPortion / (targetPercent / 100);
                suggestedPriceEl.textContent = suggestedPrice.toFixed(2);
            } else {
                suggestedPriceEl.textContent = '--';
            }
        }

        /* =========================
           INPUT LISTENERS
        ========================== */
        [
            portionsInput,
            menuPriceInput,
            taxInput,
            otherCostInput,
            wastageInput,
            laborInput,
            overheadInput,
            targetPercentInput
        ].forEach(input => {
            input?.addEventListener('input', calculateAdvanced);
        });

    });

});
