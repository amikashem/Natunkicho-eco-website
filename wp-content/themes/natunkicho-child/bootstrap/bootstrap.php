<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once NKRP_PLUGIN_PATH . 'app/Core/Autoloader.php';
require_once NKRP_PLUGIN_PATH . 'app/Core/Module.php';

$autoloader = new NKRecruitment\Core\Autoloader(
    NKRP_PLUGIN_PATH . 'app'
);

$autoloader->register();

$app = new NKRecruitment\Core\Application();

$app->boot();