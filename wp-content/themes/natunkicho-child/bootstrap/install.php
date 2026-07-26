<?php

declare(strict_types=1);

use NKRecruitment\Database\MigrationManager;
use NKRecruitment\Database\Migrations\CreateCompaniesTable;

if (!defined('ABSPATH')) {
    exit;
}

$manager = new MigrationManager();

$manager

    ->add(CreateCompaniesTable::class)

    ->run();