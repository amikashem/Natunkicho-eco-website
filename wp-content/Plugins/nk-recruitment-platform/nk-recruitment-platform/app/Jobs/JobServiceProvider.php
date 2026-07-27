<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs;

use NKRecruitment\Core\ServiceProvider;
use NKRecruitment\Jobs\Admin\JobAdmin;
use NKRecruitment\Jobs\MetaBoxes\JobMetaBox;

use NKRecruitment\Jobs\Shortcodes\PostJobShortcode; 
// NEW: Import the Job Details Shortcode Controller
use NKRecruitment\Jobs\Shortcodes\JobDetailsShortcode; 
//use NKRecruitment\Jobs\Shortcodes\EditJobShortcode;

if (!defined('ABSPATH')) {
    exit;
}

class JobServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
        (new JobMetaBox())->register();
        (new JobAdmin())->register();

        // Register the frontend shortcodes
        (new PostJobShortcode())->register();
        (new JobDetailsShortcode())->register();
        //(new EditJobShortcode())->register();// NEW: Registered!
    }

    public function registerPostType(): void
    {
        register_post_type('nk_job', [
            'labels' => [
                'name' => __('Jobs', 'nk-recruitment'),
                'singular_name' => __('Job', 'nk-recruitment'),
                'menu_name' => __('Jobs', 'nk-recruitment'),
                'add_new' => __('Add Job', 'nk-recruitment'),
                'add_new_item' => __('Add New Job', 'nk-recruitment'),
                'edit_item' => __('Edit Job', 'nk-recruitment'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => [
                'title',
                'editor',
                'thumbnail',
            ],
            'menu_icon' => 'dashicons-businessman',
        ]);
    }
}