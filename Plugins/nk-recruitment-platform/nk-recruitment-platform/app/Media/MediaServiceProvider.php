<?php

declare(strict_types=1);

namespace NKRecruitment\Media;

use NKRecruitment\Core\ServiceProvider;

if (!defined('ABSPATH')) {
    exit;
}

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        add_action(
            'admin_enqueue_scripts',
            [$this, 'assets']
        );
    }

    public function assets(): void
    {
        if (!isset($_GET['page'])) {
            return;
        }

        $pages = [

            'nkrp-company-create',

            'nkrp-company-edit'

        ];

        if (!in_array($_GET['page'], $pages, true)) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_script(

            'nkrp-media',

            NKRP_PLUGIN_URL .
            'app/Media/Assets/js/media-uploader.js',

            ['jquery'],

            NKRP_VERSION,

            true

        );
    }
}