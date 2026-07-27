<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\MetaBoxes;

if (!defined('ABSPATH')) {
    exit;
}

class JobMetaBox
{
    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_nk_job', [$this, 'save']);
    }

    public function addMetaBoxes(): void
    {
        add_meta_box(
            'nkrp_job_information',
            __('Job Information', 'nk-recruitment'),
            [$this, 'render'],
            'nk_job',
            'normal',
            'high'
        );
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field('nkrp_job_meta', 'nkrp_job_meta_nonce');

        $data = [
            'company'            => get_post_meta($post->ID, '_nkrp_company', true),
            'vacancies'          => get_post_meta($post->ID, '_nkrp_vacancies', true),
            'deadline'           => get_post_meta($post->ID, '_nkrp_deadline', true),
            'salary'             => get_post_meta($post->ID, '_nkrp_salary', true),
            'salary_min'         => get_post_meta($post->ID, '_nkrp_salary_min', true),
            'salary_max'         => get_post_meta($post->ID, '_nkrp_salary_max', true),
            'salary_type'        => get_post_meta($post->ID, '_nkrp_salary_type', true),
            'type'               => get_post_meta($post->ID, '_nkrp_type', true),
            
            // WE ADDED THE RENDER LINE HERE:
            'notification_email' => get_post_meta($post->ID, '_nkrp_notification_email', true), 
        ];

        require NKRP_PLUGIN_PATH . 'app/Jobs/Views/job-information.php';
    }

    public function save(int $postId): void
    {
        if (!isset($_POST['nkrp_job_meta_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['nkrp_job_meta_nonce'], 'nkrp_job_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        update_post_meta($postId, '_nkrp_company', sanitize_text_field($_POST['nkrp_company'] ?? ''));
        update_post_meta($postId, '_nkrp_vacancies', absint($_POST['nkrp_vacancies'] ?? 0));
        update_post_meta($postId, '_nkrp_deadline', sanitize_text_field($_POST['nkrp_deadline'] ?? ''));
        update_post_meta($postId, '_nkrp_salary', sanitize_text_field($_POST['nkrp_salary'] ?? ''));
        update_post_meta($postId, '_nkrp_salary_min', sanitize_text_field($_POST['nkrp_salary_min'] ?? ''));
        update_post_meta($postId, '_nkrp_salary_max', sanitize_text_field($_POST['nkrp_salary_max'] ?? ''));
        update_post_meta($postId, '_nkrp_salary_type', sanitize_text_field($_POST['nkrp_salary_type'] ?? ''));
        update_post_meta($postId, '_nkrp_type', sanitize_text_field($_POST['nkrp_type'] ?? ''));
        
        // WE ADDED THE SAVE LINE HERE:
        update_post_meta($postId, '_nkrp_notification_email', sanitize_email($_POST['notification_email'] ?? '')); 
    }
}