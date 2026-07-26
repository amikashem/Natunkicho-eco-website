<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class JobAdmin
{
    public function register(): void
    {
        add_action('admin_head', [$this, 'hideDefaultBoxes']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
    }

    public function assets(): void
    {
        global $post_type;

        if ($post_type !== 'nk_job') {
            return;
        }

        wp_enqueue_style(
            'nkrp-job-editor',
            NKRP_PLUGIN_URL . 'app/Jobs/Assets/css/job-editor.css',
            [],
            NKRP_VERSION
        );
    }

    public function hideDefaultBoxes(): void
    {
        global $post_type;

        if ($post_type !== 'nk_job') {
            return;
        }

        remove_meta_box('slugdiv', 'nk_job', 'normal');
        remove_meta_box('authordiv', 'nk_job', 'normal');
        remove_meta_box('commentsdiv', 'nk_job', 'normal');
        remove_meta_box('commentstatusdiv', 'nk_job', 'normal');
        remove_meta_box('trackbacksdiv', 'nk_job', 'normal');
        remove_meta_box('revisionsdiv', 'nk_job', 'normal');
        remove_meta_box('postcustom', 'nk_job', 'normal');
    }
}