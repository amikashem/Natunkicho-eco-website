<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * PHASE 5: AI SALARY INSIGHTS & GLOBAL COST OF LIVING ENGINE (With Fallback)
 * =========================================================================
 */

// 0. The Centralized AI Fallback Engine (OpenAI -> Gemini)
function nk_salary_ai_fetch_with_fallback($prompt) {
    $api_key = defined('NKJP_OPENAI') ? NKJP_OPENAI : (defined('nkjp_openai_key') ? nkjp_openai_key : '');
    if (empty($api_key)) return '';

    $url = 'https://openrouter.ai/api/v1/chat/completions';
    $headers = [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json'
    ];

    // ATTEMPT 1: OpenAI (Primary)
    $response = wp_remote_post($url, [
        'headers' => $headers,
        'body'    => wp_json_encode(['model' => 'openai/gpt-3.5-turbo', 'messages' => [['role' => 'user', 'content' => $prompt]]]),
        'timeout' => 15
    ]);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    $content = isset($data['choices'][0]['message']['content']) ? trim($data['choices'][0]['message']['content']) : '';

    // FALLBACK TRIGGER
    if (empty($content) || is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        // ATTEMPT 2: Google Gemini (Failover)
        $fallback_response = wp_remote_post($url, [
            'headers' => $headers,
            'body'    => wp_json_encode(['model' => 'google/gemini-1.5-pro', 'messages' => [['role' => 'user', 'content' => $prompt]]]),
            'timeout' => 15
        ]);
        
        $body = wp_remote_retrieve_body($fallback_response);
        $data = json_decode($body, true);
        $content = isset($data['choices'][0]['message']['content']) ? trim($data['choices'][0]['message']['content']) : '';
    }

    return $content;
}

// 1. The Global Cost of Living Estimator (With 10X Currency Sync)

function nk_get_or_estimate_cost_of_living($country, $target_currency = '') {
    global $wpdb;
    $table_col = $wpdb->prefix . 'nk_cost_of_living';

    // A. Check if we already have this country IN THE EXACT MATCHING CURRENCY
    if (!empty($target_currency)) {
        $col_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_col WHERE country = %s AND currency = %s LIMIT 1", $country, $target_currency));
    } else {
        $col_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_col WHERE country = %s LIMIT 1", $country));
    }
    
    if ($col_data) return $col_data;

    // B. Use Fallback Engine to estimate it with STRICT scale rules
    $currency_rule = !empty($target_currency) ? "CRITICAL: You MUST provide all monetary values in EXACTLY this currency: {$target_currency}." : "CRITICAL: Use the official local currency.";

    $prompt = "Act as an expert global economist and expat relocation specialist. Provide a highly realistic, current average monthly cost of living for a single working professional renting a standard 1-bedroom apartment in a major city in '{$country}'. 
    {$currency_rule}
    CRITICAL SCALING RULE: Ensure your numbers are accurately scaled for the currency requested. (e.g., If JPY, rent is tens of thousands like 80000, not 800. If IDR, it is in millions). Do not underestimate rent.
    Return ONLY a valid JSON object with these exact keys: 'rent_est' (number), 'food_est' (number), 'transport_est' (number), 'currency' (3-letter code). Do not include commas in the numbers, markdown, or extra text.";

    $ai_text = nk_salary_ai_fetch_with_fallback($prompt);

    if (!empty($ai_text)) {
        preg_match('/\{.*\}/s', $ai_text, $matches);
        $json_string = !empty($matches) ? $matches[0] : $ai_text;
        $estimates = json_decode($json_string, true);

        if (is_array($estimates) && isset($estimates['rent_est'])) {
            $wpdb->insert($table_col, [
                'country'       => sanitize_text_field($country),
                'city'          => 'National Average',
                'rent_est'      => floatval($estimates['rent_est']),
                'food_est'      => floatval($estimates['food_est']),
                'transport_est' => floatval($estimates['transport_est']),
                'currency'      => sanitize_text_field($estimates['currency'])
            ]);
            return (object) $estimates;
        }
    }
    return null;
}
// 2. AI Market Trend Generator (AJAX)
add_action('wp_ajax_nk_get_salary_insight', 'nk_ajax_get_salary_insight');
add_action('wp_ajax_nopriv_nk_get_salary_insight', 'nk_ajax_get_salary_insight');
function nk_ajax_get_salary_insight() {
    $position = sanitize_text_field($_POST['position']);
    $country  = sanitize_text_field($_POST['country']);
    if (empty($position) || empty($country)) wp_send_json_error();

    $transient_name = 'nk_ai_insight_' . md5($position . $country);
    $cached_insight = get_transient($transient_name);
    if ($cached_insight) wp_send_json_success($cached_insight);

    $prompt = "Write a 2-sentence market insight about the hospitality job market for a '{$position}' in '{$country}'. Mention hiring demand or general salary trends. Keep it highly professional and encouraging.";

    $insight = nk_salary_ai_fetch_with_fallback($prompt);

    if (!empty($insight)) {
        set_transient($transient_name, $insight, 30 * DAY_IN_SECONDS);
        wp_send_json_success($insight);
    }
    
    wp_send_json_error();
} 

// 3. The Hybrid Fallback Engine: Estimate Salary if DB is empty
function nk_get_or_estimate_salary($position, $country) {
    global $wpdb;
    $table_agg = $wpdb->prefix . 'nk_salary_aggregates';

    // FIX 1: EXACT MATCH (=) instead of LIKE (%)
    $stats = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table_agg 
        WHERE position = %s AND country = %s
    ", $position, $country));

    if ($stats && $stats->sample_size > 0) {
        $stats->is_estimated = false;
        return $stats; 
    }

    if ($stats && $stats->sample_size == 0) {
        $stats->is_estimated = true;
        return $stats; 
    }

    $prompt = "You are a global hospitality data scientist with access to real-time internet data. Estimate the current average, minimum, and maximum MONTHLY salary for a '{$position}' in '{$country}'. 
    CRITICAL INSTRUCTION: Many countries (like Japan, UK, USA, Australia) advertise hospitality jobs at an HOURLY rate (e.g., 1,000 JPY/hr). You MUST calculate the MONTHLY equivalent by multiplying the hourly rate by 160 hours/month. NEVER return an hourly rate in the JSON. 
    Return ONLY a valid JSON object with these exact keys: 'avg_salary' (number), 'min_salary' (number), 'max_salary' (number), 'currency' (3-letter code, e.g. JPY). Do not include any text or markdown formatting.";

    $ai_text = nk_salary_ai_fetch_with_fallback($prompt);

    if (!empty($ai_text)) {
        // FIX 2: Bulletproof JSON Extractor
        preg_match('/\{.*\}/s', $ai_text, $matches);
        $json_string = !empty($matches) ? $matches[0] : $ai_text;
        $estimates = json_decode($json_string, true);

        if (is_array($estimates) && isset($estimates['avg_salary'])) {
            $wpdb->replace($table_agg, [
                'position'    => sanitize_text_field($position),
                'country'     => sanitize_text_field($country),
                'avg_salary'  => floatval($estimates['avg_salary']),
                'min_salary'  => floatval($estimates['min_salary']),
                'max_salary'  => floatval($estimates['max_salary']),
                'sample_size' => 0, 
                'currency'    => sanitize_text_field($estimates['currency'])
            ], ['%s', '%s', '%f', '%f', '%f', '%d', '%s']);

            $estimates['is_estimated'] = true;
            $estimates['sample_size'] = 0;
            return (object) $estimates;
        }
    }
    return null;
}