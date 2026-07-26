<?php if (!defined('ABSPATH')) exit; ?>

<style>
    .nk-compare-wrapper { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); margin: 60px 0; }
    .nk-compare-header { text-align: center; margin-bottom: 30px; }
    .nk-compare-header h3 { font-size: 28px; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; }
    .nk-compare-grid { display: grid; grid-template-columns: 1fr 50px 1fr; gap: 20px; align-items: center; }
    .nk-compare-col { background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; }
    .nk-compare-vs { font-size: 24px; font-weight: 900; color: #cbd5e1; text-align: center; }
    .nk-compare-input { width: 100%; height: 50px; padding: 0 15px; margin-bottom: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 15px; outline: none; box-sizing: border-box; }
    .nk-compare-input:focus { border-color: #8b5cf6; }
    .nk-compare-btn { background: #0f172a; color: #fff; border: none; padding: 15px 40px; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.2s; margin-top: 30px; width: 100%; }
    .nk-compare-btn:hover { background: #1e293b; }
    .nk-compare-result { font-size: 32px; font-weight: 900; color: #10b981; margin-top: 20px; }

    @media (max-width: 768px) {
        .nk-compare-grid { grid-template-columns: 1fr; }
        .nk-compare-vs { padding: 10px 0; }
    }
</style>

<div class="nk-compare-wrapper">
    <div class="nk-compare-header">
        <h3>⚖️ Market Comparison Tool</h3>
        <p style="color: #64748b;">Compare salaries between different hospitality roles or different countries.</p>
    </div>

    <form id="nk-compare-form" onsubmit="nkRunComparison(event)">
        <div class="nk-compare-grid">
            
            <div class="nk-compare-col" style="border-top: 4px solid #0A66C2;">
                <h4 style="margin: 0 0 15px 0; color: #0A66C2;">Scenario A</h4>
                <input type="text" id="comp_posA" class="nk-compare-input" placeholder="Role (e.g. Sous Chef)" required>
                <input type="text" id="comp_locA" class="nk-compare-input" placeholder="Country (e.g. Maldives)" required>
                <div class="nk-compare-result" id="resA">---</div>
            </div>

            <div class="nk-compare-vs">VS</div>

            <div class="nk-compare-col" style="border-top: 4px solid #8b5cf6;">
                <h4 style="margin: 0 0 15px 0; color: #8b5cf6;">Scenario B</h4>
                <input type="text" id="comp_posB" class="nk-compare-input" placeholder="Role (e.g. Sous Chef)" required>
                <input type="text" id="comp_locB" class="nk-compare-input" placeholder="Country (e.g. Dubai)" required>
                <div class="nk-compare-result" id="resB">---</div>
            </div>

        </div>
        <button type="submit" class="nk-compare-btn">Run Comparison</button>
    </form>
</div>

<script>
function nkRunComparison(e) {
    e.preventDefault();
    document.getElementById('resA').innerText = "Loading...";
    document.getElementById('resB').innerText = "Loading...";

    let formData = new FormData();
    formData.append('action', 'nk_compare_salaries');
    formData.append('posA', document.getElementById('comp_posA').value);
    formData.append('locA', document.getElementById('comp_locA').value);
    formData.append('posB', document.getElementById('comp_posB').value);
    formData.append('locB', document.getElementById('comp_locB').value);

    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById('resA').innerText = data.data.A;
            document.getElementById('resB').innerText = data.data.B;
        }
    });
}
</script>