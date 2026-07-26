<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * PHASE 3: SALARY SHORTCODES (Search & Discovery Grids)
 * =========================================================================
 */

// 1. The Dynamic Salary Search Widget
add_shortcode('nk_salary_search', 'nk_salary_search_shortcode');
function nk_salary_search_shortcode() {
    ob_start();
    ?>
    <style>
        .nk-salary-search-box { background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; max-width: 800px; margin: -100px auto 50px auto; position: relative; z-index: 10; }
        .nk-salary-search-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .nk-sal-input-group { flex: 1; min-width: 220px; text-align: left; }
        .nk-sal-input-group label { display: block; font-size: 14px; font-weight: 700; color: #475569; margin-bottom: 8px; }
        .nk-sal-input-group input, .nk-sal-input-group select { width: 100%; height: 55px; padding: 0 20px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; color: #0f172a; outline: none; transition: 0.2s; box-sizing: border-box; }
        .nk-sal-input-group input:focus, .nk-sal-input-group select:focus { border-color: #0A66C2; box-shadow: 0 0 0 3px rgba(10,102,194,0.1); }
        .nk-sal-btn { background: #0A66C2; color: #fff; border: none; padding: 0 40px; height: 55px; border-radius: 8px; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.2s; white-space: nowrap; }
        .nk-sal-btn:hover { background: #08529e; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(10,102,194,0.2); }
    </style>

    <div class="nk-salary-search-box">
        <form id="nk-salary-search-form" class="nk-salary-search-form" onsubmit="nkProcessSalarySearch(event)">
            <div class="nk-sal-input-group">
                <label>Job Title / Position</label>
                <input type="text" id="sal_position" placeholder="e.g. Sous Chef, Front Desk..." required>
            </div>
            <div class="nk-sal-input-group">
                <label>Country / Location</label>
                <input type="text" id="sal_country" placeholder="e.g. Dubai, Maldives..." required>
            </div>
            <button type="submit" class="nk-sal-btn">Check Salary</button>
        </form>
    </div>

    <script>
        function nkProcessSalarySearch(e) {
            e.preventDefault();
            // Grab values and clean them for the URL (lowercase, replace spaces/symbols with hyphens)
            let pos = document.getElementById('sal_position').value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-');
            let loc = document.getElementById('sal_country').value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-');
            
            // Redirect to the Nuclear Router URL
            if(pos && loc) {
                window.location.href = '/salary/' + pos + '/' + loc + '/';
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

// 2. The Dynamic Popular Salaries Grid
add_shortcode('nk_salary_grid', 'nk_salary_grid_shortcode');
function nk_salary_grid_shortcode() {
    global $wpdb;
    $table_agg = $wpdb->prefix . 'nk_salary_aggregates';

    // 10X LOGIC: Fetch the Top 6 most popular salary reports based on the amount of data we have!
    $top_salaries = $wpdb->get_results("
        SELECT position, country 
        FROM $table_agg 
        ORDER BY sample_size DESC 
        LIMIT 6
    ");

    // Fallback: If the database is completely empty (brand new site), show these defaults
    if (empty($top_salaries)) {
        $top_salaries = [
            (object)['position' => 'Sous Chef', 'country' => 'United Arab Emirates'],
            (object)['position' => 'Restaurant Manager', 'country' => 'Qatar'],
            (object)['position' => 'Front Desk Agent', 'country' => 'Maldives'],
            (object)['position' => 'Executive Housekeeper', 'country' => 'Saudi Arabia'],
            (object)['position' => 'Bartender', 'country' => 'Singapore'],
            (object)['position' => 'Commis Chef', 'country' => 'Bahrain'],
        ];
    }

    ob_start();
    ?>
    <style>
        .nk-sal-grid-section { margin-top: 60px; }
        .nk-sal-grid-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
        .nk-sal-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .nk-sal-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; text-decoration: none; transition: 0.3s; display: flex; align-items: center; justify-content: space-between; }
        .nk-sal-card:hover { border-color: #10b981; box-shadow: 0 10px 30px rgba(16,185,129,0.1); transform: translateY(-3px); }
        .nk-sal-card-info h4 { margin: 0 0 5px 0; color: #0f172a; font-size: 18px; font-weight: 700; }
        .nk-sal-card-info p { margin: 0; color: #64748b; font-size: 14px; }
        .nk-sal-card-arrow { color: #10b981; font-weight: bold; font-size: 20px; }
    </style>

    <div class="nk-sal-grid-section">
        <h2 class="nk-sal-grid-title">🔥 Trending Market Reports</h2>
        <div class="nk-sal-grid">
            <?php foreach ($top_salaries as $sal) : 
                // Dynamically format the URLs so they match our Nuclear Router perfectly
                $pos_url = strtolower(str_replace(' ', '-', $sal->position));
                $loc_url = strtolower(str_replace(' ', '-', $sal->country));
                $url = site_url("/salary/{$pos_url}/{$loc_url}/");
            ?>
                <a href="<?php echo esc_url($url); ?>" class="nk-sal-card">
                    <div class="nk-sal-card-info">
                        <h4><?php echo esc_html($sal->position); ?></h4>
                        <p><?php echo esc_html($sal->country); ?></p>
                    </div>
                    <div class="nk-sal-card-arrow">→</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
} 

// 3. The Salary Comparison Tool Shortcode
add_shortcode('nk_salary_compare', 'nk_salary_compare_shortcode');
function nk_salary_compare_shortcode() {
    ob_start();
    // Load the visual UI file
    include(get_stylesheet_directory() . '/template-parts/salaries/calc-compare.php');
    return ob_get_clean();
}

// 4. AJAX Endpoint for the Compare Tool to fetch real-time data
add_action('wp_ajax_nk_compare_salaries', 'nk_ajax_compare_salaries');
add_action('wp_ajax_nopriv_nk_compare_salaries', 'nk_ajax_compare_salaries');
function nk_ajax_compare_salaries() {
    global $wpdb;
    $posA = sanitize_text_field($_POST['posA']);
    $locA = sanitize_text_field($_POST['locA']);
    $posB = sanitize_text_field($_POST['posB']);
    $locB = sanitize_text_field($_POST['locB']);

    $table_agg = $wpdb->prefix . 'nk_salary_aggregates';
    
    // Fetch Data A
    $dataA = $wpdb->get_row($wpdb->prepare("SELECT avg_salary, currency FROM $table_agg WHERE position LIKE %s AND country LIKE %s", '%' . $wpdb->esc_like($posA) . '%', '%' . $wpdb->esc_like($locA) . '%'));
    // Fetch Data B
    $dataB = $wpdb->get_row($wpdb->prepare("SELECT avg_salary, currency FROM $table_agg WHERE position LIKE %s AND country LIKE %s", '%' . $wpdb->esc_like($posB) . '%', '%' . $wpdb->esc_like($locB) . '%'));

    wp_send_json_success([
        'A' => $dataA ? number_format($dataA->avg_salary) . ' ' . $dataA->currency : 'No Data Yet',
        'B' => $dataB ? number_format($dataB->avg_salary) . ' ' . $dataB->currency : 'No Data Yet'
    ]);
}