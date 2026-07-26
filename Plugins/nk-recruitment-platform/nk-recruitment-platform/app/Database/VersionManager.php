<?php

declare(strict_types=1);

namespace NKRecruitment\Database;

if (!defined('ABSPATH')) {
    exit;
}

class VersionManager
{
    private const OPTION = 'nkrp_db_version';

    public static function current(): int
    {
        return (int) get_option(self::OPTION, 0);
    }

    public static function set(int $version): void
    {
        update_option(self::OPTION, $version);
    }

    public static function needsUpgrade(int $version): bool
    {
        return self::current() < $version;
    }
}