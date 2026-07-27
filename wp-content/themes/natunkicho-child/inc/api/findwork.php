<?php
if (!defined('ABSPATH')) exit;

function nk_fetch_findwork_jobs($keywords = '', $location = '') {
    $jobs = [];
    $api_key = defined('NKJP_FINDWORK_API_KEY') ? NKJP_FINDWORK_API_KEY : '';
    $url = add_query_arg([
        'search' => !empty($keywords) ? $keywords : 'hospitality',
        'limit' => 6,
    ], 'https://findwork.dev/api/jobs/');

    $response = wp_remote_get($url, [
        'headers' => ['Authorization' => 'Token ' . $api_key]
    ]);

    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['results'])) {
            foreach ($data['results'] as $job) {
                // FALLBACK LOGIC: Try multiple keys for description
                $desc = $job['text'] ?? $job['description'] ?? 'No specific description provided for this role. Click View Job for details.';
                $jobs[] = [
                    'id'       => $job['id'] ?? uniqid(),
                    'title'    => $job['role'] ?? 'Hospitality Job',
                    'company'  => $job['company_name'] ?? 'Company',
                    'location' => $job['location'] ?? 'Remote',
                    'description' => $job['description'] ?? '',
                    'url'      => $job['url'] ?? '#',
                    'source'   => 'Findwork'
                ];
            }
        }
    }
    return $jobs;
}