<?php
if (!defined('ABSPATH')) exit;

// Register the Custom Dual Search Shortcode
function nk_global_dual_search_shortcode() {
    ob_start(); 
    ?>
    <style>
        .nk-global-search-wrapper { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; max-width: 900px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .nk-global-search-wrapper .nk-search-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        .nk-global-search-wrapper .nk-dash-search-tab { background: none; border: none; padding: 10px 20px; font-size: 15px; font-weight: 700; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.2s; margin-bottom: -11px; }
        .nk-global-search-wrapper .nk-dash-search-tab.active { color: #0A66C2; border-bottom-color: #0A66C2; }
        .nk-global-search-wrapper .nk-dash-search-form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .nk-global-search-wrapper .nk-search-input-group { flex: 1; min-width: 200px; position: relative; }
        .nk-global-search-wrapper .nk-search-input-group input, 
        .nk-global-search-wrapper .nk-search-input-group select { width: 100%; height: 50px; padding: 0 20px 0 45px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; color: #0f172a; outline: none; background: #f8fafc; box-sizing: border-box; }
        .nk-global-search-wrapper .nk-search-input-group input:focus, 
        .nk-global-search-wrapper .nk-search-input-group select:focus { border-color: #0A66C2; background: #fff; }
        .nk-global-search-wrapper .nk-search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8; }
        .nk-global-search-wrapper .nk-search-btn { background: #0A66C2; color: #fff; border: none; padding: 0 35px; height: 50px; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; white-space: nowrap; }
        .nk-global-search-wrapper .nk-search-btn:hover { background: #08529e; }
        .nk-global-search-wrapper .btn-learning { background: #8b5cf6; }
        .nk-global-search-wrapper .btn-learning:hover { background: #6d28d9; }
    </style>

    <div class="nk-global-search-wrapper">
        <div class="nk-search-tabs">
            <button class="nk-dash-search-tab active" onclick="nkGlobalSwitchSearch('jobs', this)">💼 Search Jobs</button>
            <button class="nk-dash-search-tab" onclick="nkGlobalSwitchSearch('learning', this)">📚 Search Learning</button>
        </div>
        
        <form action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="nk-dash-search-form nk-form-jobs">
            <input type="hidden" name="post_type" value="job_listing"> 
            <div class="nk-search-input-group">
                <span class="nk-search-icon">🔍</span>
                <input type="text" name="s" placeholder="Job Title (e.g. Head Chef)" > 
            </div>
            <div class="nk-search-input-group">
                <span class="nk-search-icon">📍</span>
                <input type="text" name="search_location" placeholder="Location (e.g. Maldives)">
            </div>
            <button type="submit" class="nk-search-btn">Find Jobs</button>
        </form>

        <form action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="nk-dash-search-form nk-form-learning" style="display:none;">
            <input type="hidden" name="post_type" value="post"> 
            <div class="nk-search-input-group">
                <span class="nk-search-icon">🔍</span>
                <input type="text" name="s" placeholder="Search Food Costing, Recipes, SOPs..."> 
            </div>
            <div class="nk-search-input-group">
                <span class="nk-search-icon">📁</span>
                <select name="category_name">
                    <option value="">All Categories</option>
                    <?php
                    $search_cats = get_categories(['hide_empty' => true]);
                    foreach($search_cats as $scat) {
                        echo '<option value="' . esc_attr($scat->slug) . '">' . esc_html($scat->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="nk-search-btn btn-learning">Search Articles</button>
        </form>
    </div>

    <script>
        function nkGlobalSwitchSearch(type, btn) {
            let wrapper = btn.closest('.nk-global-search-wrapper');
            let tabs = wrapper.querySelectorAll('.nk-dash-search-tab');
            tabs.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            
            if(type === 'jobs') {
                wrapper.querySelector('.nk-form-jobs').style.display = 'flex';
                wrapper.querySelector('.nk-form-learning').style.display = 'none';
            } else {
                wrapper.querySelector('.nk-form-jobs').style.display = 'none';
                wrapper.querySelector('.nk-form-learning').style.display = 'flex';
            }
        }
    </script>
    <?php
    return ob_get_clean(); 
}
add_shortcode('nk_dual_search', 'nk_global_dual_search_shortcode');