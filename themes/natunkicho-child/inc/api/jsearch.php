<?php
if (!defined('ABSPATH')) exit;

function nk_fetch_jsearch_jobs($keywords = '', $location = '') {
    $jobs = [];
    $api_key = defined('NKJP_JSEARCH_API_KEY') ? NKJP_JSEARCH_API_KEY : '';
    $search = !empty($keywords) ? $keywords : 'hospitality';
    if (!empty($location)) $search .= ' in ' . $location;
    
    $url = "https://jsearch.p.rapidapi.com/search?query=" . urlencode($search) . "&page=1&num_pages=1";
    $response = wp_remote_get($url, [
        'headers' => ['X-RapidAPI-Key' => $api_key, 'X-RapidAPI-Host' => 'jsearch.p.rapidapi.com']
    ]);

    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['data'])) {
            foreach ($data['data'] as $job) {
                // FALLBACK LOGIC: Try multiple keys for description
                $desc = $job['text'] ?? $job['description'] ?? 'No specific description provided for this role. Click View Job for details.';
                $jobs[] = [
                    'id'       => $job['job_id'] ?? uniqid(),
                    'title'    => $job['job_title'] ?? 'Hospitality Job',
                    'company'  => $job['employer_name'] ?? 'Company',
                    'location' => $job['job_city'] ?? 'Location',
                    'description' => $job['description'] ?? '',
                    'url'      => $job['job_apply_link'] ?? '#',
                    'source'   => 'JSearch'
                ];
            }
        }
    }
    return $jobs;
}