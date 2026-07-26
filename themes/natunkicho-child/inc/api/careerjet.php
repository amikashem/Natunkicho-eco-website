<?php
if (!defined('ABSPATH')) exit;

function nk_fetch_careerjet_jobs($keywords = '', $location = '') {
    $jobs = [];
    $affid = defined('NKJP_CAREERJET_AFFID') ? NKJP_CAREERJET_AFFID : '';
    $url = add_query_arg([
        'affid' => $affid,
        'keywords' => !empty($keywords) ? $keywords : 'hospitality',
        'location' => $location,
        'pagesize' => 6,
        'page' => 1,
        'locale_code' => 'en_GB',
    ], 'https://public.api.careerjet.net/search');

    $response = wp_remote_get($url, ['timeout' => 20]);
    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['jobs'])) {
            foreach ($data['jobs'] as $job) {
                // FALLBACK LOGIC: Try multiple keys for description
                $desc = $job['text'] ?? $job['description'] ?? 'No specific description provided for this role. Click View Job for details.';
                $jobs[] = [
                    'id'       => uniqid(),
                    'title'    => $job['title'] ?? 'Hospitality Job',
                    'company'  => $job['company'] ?? 'Company',
                    'location' => $job['locations'] ?? 'Location',
                    'description' => $job['description'] ?? '',
                    'url'      => $job['url'] ?? '#',
                    'source'   => 'Careerjet'
                ];
            }
        }
    }
    return $jobs;
}