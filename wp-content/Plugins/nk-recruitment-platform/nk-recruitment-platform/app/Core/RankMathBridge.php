<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class RankMathBridge
{
    public function register(): void
    {
        // Only run if Rank Math is actually active
        if (!class_exists('RankMath')) {
            return;
        }

        // 1. Hook into Rank Math's Title & Description
        add_filter('rank_math/frontend/title', [$this, 'dynamicTitle'], 10, 1);
        add_filter('rank_math/frontend/description', [$this, 'dynamicDescription'], 10, 1);
        
        // 2. Hook into Rank Math's Social Media Cards (OpenGraph / Twitter)
        add_filter('rank_math/opengraph/facebook/title', [$this, 'dynamicTitle'], 10, 1);
        add_filter('rank_math/opengraph/twitter/title', [$this, 'dynamicTitle'], 10, 1);
        add_filter('rank_math/opengraph/facebook/description', [$this, 'dynamicDescription'], 10, 1);
        add_filter('rank_math/opengraph/twitter/description', [$this, 'dynamicDescription'], 10, 1);
        add_filter('rank_math/opengraph/facebook/image', [$this, 'dynamicImage'], 10, 1);
        add_filter('rank_math/opengraph/twitter/image', [$this, 'dynamicImage'], 10, 1);

        // 3. Inject Google JobPosting Schema into Rank Math's JSON-LD
        add_filter('rank_math/json_ld', [$this, 'injectGoogleJobSchema'], 99, 2);
    }

    private function getContextData()
    {
        global $wpdb;
        $job_slug = get_query_var('job_slug');
        $company_slug = get_query_var('company_slug');

        if (!empty($job_slug)) {
            $table = DatabaseManager::table('jobs');
            $compTable = DatabaseManager::table('companies');
            return $wpdb->get_row($wpdb->prepare("
                SELECT j.*, c.company_name, c.logo 
                FROM {$table} j 
                LEFT JOIN {$compTable} c ON j.company_id = c.id 
                WHERE j.job_slug = %s
            ", $job_slug));
        }

        if (!empty($company_slug)) {
            $table = DatabaseManager::table('companies');
            return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE company_slug = %s", $company_slug));
        }

        return null;
    }

    public function dynamicTitle($title)
    {
        $data = $this->getContextData();
        if ($data) {
            if (isset($data->job_title)) {
                return esc_html($data->job_title) . ' at ' . esc_html($data->company_name ?? 'Confidential') . ' - ' . get_bloginfo('name');
            }
            if (isset($data->company_name)) {
                return esc_html($data->company_name) . ' Careers - ' . get_bloginfo('name');
            }
        }
        return $title;
    }

    public function dynamicDescription($description)
    {
        $data = $this->getContextData();
        if ($data && isset($data->description)) {
            return wp_trim_words(strip_tags(stripslashes((string)$data->description)), 25, '...');
        }
        return $description;
    }

    public function dynamicImage($image)
    {
        $data = $this->getContextData();
        if ($data && isset($data->logo)) {
            $logo_url = is_numeric($data->logo) ? wp_get_attachment_image_url((int)$data->logo, 'large') : $data->logo;
            if (!empty($logo_url)) return $logo_url;
        }
        return $image;
    }

    /**
     * 🔥 THE 10X FEATURE: Passes your custom table data into Rank Math's Google Schema Generator!
     */
    public function injectGoogleJobSchema($data, $jsonld)
    {
        $job_slug = get_query_var('job_slug');
        if (empty($job_slug)) return $data;

        $job = $this->getContextData();
        if (!$job) return $data;

        $logo_url = is_numeric($job->logo) ? wp_get_attachment_image_url((int)$job->logo, 'large') : $job->logo;

        // Map your database job types to Google's strict requirements
        $emp_type_map = [
            'Full-Time' => 'FULL_TIME',
            'Part-Time' => 'PART_TIME',
            'Contract' => 'CONTRACTOR',
            'Temporary' => 'TEMPORARY',
            'Internship' => 'INTERN',
            'Freelance' => 'CONTRACTOR'
        ];
        $google_emp_type = $emp_type_map[$job->job_type ?? ''] ?? 'FULL_TIME';

        $data['JobPosting'] = [
            '@type' => 'JobPosting',
            'title' => $job->job_title,
            'description' => wp_kses_post($job->description),
            'datePosted' => gmdate('Y-m-d\TH:i:s\Z', strtotime($job->created_at)),
            'employmentType' => $google_emp_type,
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $job->company_name ?: 'Confidential',
                'sameAs' => get_bloginfo('url'),
                'logo' => $logo_url ?: get_site_icon_url()
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $job->city ?? '',
                    'addressCountry' => $job->country ?? ''
                ]
            ]
        ];

        if (!empty($job->deadline)) {
            $data['JobPosting']['validThrough'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($job->deadline));
        }

        return $data;
    }
}