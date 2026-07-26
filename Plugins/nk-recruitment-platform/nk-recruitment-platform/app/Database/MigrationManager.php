<?php

declare(strict_types=1);

namespace NKRecruitment\Database;

class MigrationManager
{
    /**
     * @var Migration[]
     */
    private array $migrations = [];

    public function add(Migration $migration): self
    {
        $this->migrations[] = $migration;

        return $this;
    }

    public function run(): void
    {
        foreach ($this->migrations as $migration) {

            $migration->up();

        }
    }
}