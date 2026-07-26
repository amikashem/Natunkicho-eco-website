<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Module
{
    abstract public function register(): void;

    public function boot(): void
    {
        // Optional
    }
}