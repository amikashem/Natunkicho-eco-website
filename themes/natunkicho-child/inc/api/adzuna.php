<?php
if (!defined('ABSPATH')) exit;

function nk_fetch_adzuna_jobs($keywords = '', $location = '') {
    $jobs = [];
    $app_id = defined('NKJP_ADZUNA_APP_ID') ? NKJP_ADZUNA_APP_ID : '';
    $api_key = defined('NKJP_ADZUNA_API_KEY') ? NKJP_ADZUNA_API_KEY : '';
    $query = !empty($keywords) ? urlencode($keywords) : 'hospitality';
    
    $url = "https://api.adzuna.com/v1/api/jobs/gb/search/1?app_id={$app_id}&app_key={$api_key}&results_per_page=6&what={$query}";
    if (!empty($location)) $url .= '&where=' . urlencode($location);

    $response = wp_remote_get($url, ['timeout' => 20]);
    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['results'])) {
            foreach ($data['results'] as $job) {
                // FALLBACK LOGIC: Try multiple keys for description
                $desc = $job['text'] ?? $job['description'] ?? 'No specific description provided for this role. Click View Job for details.';
                $jobs[] = [
                    'id'       => $job['id'] ?? uniqid(),
                    'title'    => $job['title'] ?? 'Hospitality Job',
                    'company'  => $job['company']['display_name'] ?? 'Company',
                    'location' => $job['location']['display_name'] ?? 'Location',
                    'description' => $job['description'] ?? '',
                    'url'      => $job['redirect_url'] ?? '#',
                    'source'   => 'Adzuna'
                ];
            }
        }
    }
    return $jobs;
}