<?php

declare(strict_types=1);

namespace NKRecruitment\Admin;

use NKRecruitment\Core\Module;

if (!defined('ABSPATH')) {
    exit;
}

class AdminServiceProvider extends Module
{
    public function register(): void
    {
        (new MenuManager())->register();
    }
}