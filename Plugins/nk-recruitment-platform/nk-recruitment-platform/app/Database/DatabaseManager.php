<?php

declare(strict_types=1);

namespace NKRecruitment\Database;

if (!defined('ABSPATH')) {
    exit;
}

class DatabaseManager
{
    public static function db(): \wpdb
    {
        global $wpdb;

        return $wpdb;
    }

    public static function prefix(): string
    {
        global $wpdb;

        return $wpdb->prefix;
    }

    public static function table(string $table): string
    {
        return self::prefix() . 'nkrp_' . $table;
    }
}