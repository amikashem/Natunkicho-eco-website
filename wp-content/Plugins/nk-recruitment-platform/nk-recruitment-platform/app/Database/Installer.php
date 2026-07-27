<?php

declare(strict_types=1);

namespace NKRecruitment\Database;

use NKRecruitment\Database\Migrations\CreateCompaniesTable;
use NKRecruitment\Database\Migrations\CreateJobsTable;
use NKRecruitment\Database\Migrations\CreateCandidatesTable;
use NKRecruitment\Database\Migrations\CreateResumesTable;
use NKRecruitment\Database\Migrations\CreateMessagesTable;
use NKRecruitment\Database\Migrations\CreateCandidateIndexTable; // <-- NEW

if (!defined('ABSPATH')) {
    exit;
}

class Installer
{
    public static function install(): void
    {
        $config = require NKRP_PLUGIN_PATH . 'config/database.php';

        if (!VersionManager::needsUpgrade($config['version'])) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        require_once NKRP_PLUGIN_PATH . 'app/Database/Migrations/CreateCompaniesTable.php';
        require_once NKRP_PLUGIN_PATH . 'app/Database/Migrations/CreateJobsTable.php';
        require_once NKRP_PLUGIN_PATH . 'app/Database/Migrations/CreateCandidatesTable.php';
        require_once NKRP_PLUGIN_PATH . 'app/Database/Migrations/CreateResumesTable.php';
        require_once NKRP_PLUGIN_PATH . 'app/Database/Migrations/CreateMessagesTable.php';
        require_once NKRP_PLUGIN_PATH . 'app/Database/Migrations/CreateCandidateIndexTable.php'; // <-- NEW

        $migration = new MigrationManager();

        $migration
            ->add(new CreateCompaniesTable())
            ->add(new CreateJobsTable())
            ->add(new CreateCandidatesTable())
            ->add(new CreateResumesTable())
            ->add(new CreateMessagesTable())
            ->add(new CreateCandidateIndexTable()) // <-- NEW
            ->run();

        VersionManager::set($config['version']);
    }
}