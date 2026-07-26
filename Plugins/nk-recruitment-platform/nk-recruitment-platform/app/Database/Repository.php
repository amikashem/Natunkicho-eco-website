<?php

declare(strict_types=1);

namespace NKRecruitment\Database;

abstract class Repository
{
    protected \wpdb $db;

    public function __construct()
    {
        $this->db = DatabaseManager::db();
    }
}