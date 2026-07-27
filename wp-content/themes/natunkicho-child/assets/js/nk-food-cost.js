/* ==================================================
   NK FOOD COST CALCULATOR CORE
   Namespace: nkFc
================================================== */

document.addEventListener("DOMContentLoaded", function () {

    /* ===============================
       SIMPLE FOOD COST CALCULATOR
    =============================== */

    const simpleBtn = document.getElementById("nk-fc-simple-calc");

    if (simpleBtn) {
        simpleBtn.addEventListener("click", function () {

            const ingredientCost = parseFloat(document.getElementById("nk-fc-simple-cost")?.value) || 0;
            const portions = parseFloat(document.getElementById("nk-fc-simple-portions")?.value) || 1;
            const sellingPrice = parseFloat(document.getElementById("nk-fc-simple-price")?.value) || 0;

            const costPerPortion = ingredientCost / portions;
            const foodCostPercent = sellingPrice > 0
                ? (costPerPortion / sellingPrice) * 100
                : 0;

            document.getElementById("nk-fc-simple-result").innerHTML = `
                <p><strong>Cost per Portion:</strong> ${costPerPortion.toFixed(2)}</p>
                <p><strong>Food Cost %:</strong> ${foodCostPercent.toFixed(2)}%</p>
            `;
        });
    }

    /* ===============================
       ADVANCED FOOD COST CALCULATOR
    =============================== */

    const addIngredientBtn = document.getElementById("nk-fc-add-ingredient");
    const ingredientWrap = document.getElementById("nk-fc-ingredients");

    if (addIngredientBtn && ingredientWrap) {
        addIngredientBtn.addEventListener("click", function () {
            ingredientWrap.appendChild(createIngredientRow());
        });
    }

    function createIngredientRow() {
        const row = document.createElement("div");
        row.className = "nk-fc-ingredient-row";

        row.innerHTML = `
            <input type="text" placeholder="Ingredient name">
            <input type="number" step="0.01" class="nk-fc-unit-cost" placeholder="Unit price">
            <input type="text" placeholder="Unit (kg/g/ltr)">
            <input type="number" step="0.01" class="nk-fc-qty" placeholder="Qty used">
            <input type="number" step="0.01" class="nk-fc-line-cost" placeholder="Cost" readonly>
        `;

        const unitCost = row.querySelector(".nk-fc-unit-cost");
        const qty = row.querySelector(".nk-fc-qty");
        const lineCost = row.querySelector(".nk-fc-line-cost");

        function updateLineCost() {
            const cost = (parseFloat(unitCost.value) || 0) * (parseFloat(qty.value) || 0);
            lineCost.value = cost.toFixed(2);
            calculateAdvancedTotals();
        }

        unitCost.addEventListener("input", updateLineCost);
        qty.addEventListener("input", updateLineCost);

        return row;
    }

    const advancedCalcBtn = document.getElementById("nk-fc-advanced-calc");

    if (advancedCalcBtn) {
        advancedCalcBtn.addEventListener("click", calculateAdvancedTotals);
    }

    function calculateAdvancedTotals() {

        const portions = parseFloat(document.getElementById("nk-fc-portions")?.value) || 1;
        const menuPrice = parseFloat(document.getElementById("nk-fc-menu-price")?.value) || 0;
        const targetFoodCost = parseFloat(document.getElementById("nk-fc-target-percent")?.value) || 30;

        let totalRecipeCost = 0;

        document.querySelectorAll(".nk-fc-line-cost").forEach(function (field) {
            totalRecipeCost += parseFloat(field.value) || 0;
        });

        const costPerPortion = totalRecipeCost / portions;

        const foodCostPercent = menuPrice > 0
            ? (costPerPortion / menuPrice) * 100
            : 0;

        const suggestedPrice = targetFoodCost > 0
            ? (costPerPortion / targetFoodCost) * 100
            : 0;

        document.getElementById("nk-fc-total-cost").innerText = totalRecipeCost.toFixed(2);
        document.getElementById("nk-fc-cost-per-portion").innerText = costPerPortion.toFixed(2);
        document.getElementById("nk-fc-food-cost-percent").innerText = foodCostPercent.toFixed(2) + "%";
        document.getElementById("nk-fc-suggested-price").innerText = suggestedPrice.toFixed(2);
    }

});
