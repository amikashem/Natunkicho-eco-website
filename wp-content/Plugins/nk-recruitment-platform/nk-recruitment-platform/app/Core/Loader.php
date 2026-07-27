<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Loader
{
    /**
     * @var Module[]
     */
    private array $modules = [];

    public function add(Module $module): self
    {
        $this->modules[] = $module;

        return $this;
    }

    public function run(): void
    {
        foreach ($this->modules as $module) {
            $module->register();
        }

        foreach ($this->modules as $module) {
            $module->boot();
        }
    }
}