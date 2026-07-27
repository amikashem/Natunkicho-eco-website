<?php

declare(strict_types=1);

namespace NKRecruitment\Database;

abstract class Migration
{
    abstract public function up(): void;

    public function down(): void
    {
    }
}