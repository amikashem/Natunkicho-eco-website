document.addEventListener("DOMContentLoaded", function () {

  const calculators = document.querySelectorAll(".nk-calculator");

  if (!calculators.length) return;

  const thawingRates = {
    chicken: 9,
    beef: 11,
    lamb: 11,
    fish: 7,
    minced: 5.5
  };

  calculators.forEach(calculator => {

    const button = calculator.querySelector(".nk-btn");
    const resultBox = calculator.querySelector(".nk-result");

    button.addEventListener("click", function () {

      const meatType = calculator.querySelector("#nk-meat-type").value;
      const weight = parseFloat(calculator.querySelector("#nk-weight").value);
      const method = calculator.querySelector("#nk-method").value;

      if (!weight || weight <= 0) {
        resultBox.innerHTML = "<p>Please enter a valid weight.</p>";
        return;
      }

      let baseTime = weight * thawingRates[meatType];
      let finalTime = baseTime;

      if (method === "water") {
        finalTime = baseTime * 0.25;
      }

      if (method === "microwave") {
               resultBox.innerHTML = `
          <strong>Estimated Safe Thawing Time</strong><br>
          ⏱️ ${hours.toFixed(1)} hours<br><br>
          <small>
          ✔ Keep meat covered<br>
          ✔ Store on lowest refrigerator shelf<br>
          ✔ Never thaw at room temperature
          </small>
        `;

        return;
      }

      resultBox.innerHTML = `
        <strong>Estimated Thawing Time:</strong><br>
        ⏱️ ${finalTime.toFixed(1)} hours<br><br>
        <em>Store meat covered on the lowest refrigerator shelf.</em>
      `;
    });

  });

});
/* =========================================
   NK CALCULATOR HUB – DROPDOWN CONTROLLER
========================================= */

document.addEventListener('DOMContentLoaded', function () {

    const selector = document.getElementById('nkCalcSelect');
    const panels = document.querySelectorAll('.nk-calculator-panel');

    if (!selector || panels.length === 0) {
        return;
    }

    // Hide all calculators initially
    panels.forEach(panel => {
        panel.style.display = 'none';
    });

    selector.addEventListener('change', function () {
        const selected = this.value;

        panels.forEach(panel => {
            if (panel.dataset.calculator === selected) {
                panel.style.display = 'block';
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                panel.style.display = 'none';
            }
        });
    });

});

