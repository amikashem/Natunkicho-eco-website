<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Autoloader
{
    private string $namespace = 'NKRecruitment\\';

    public function __construct(
        private string $basePath
    ) {
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    private function load(string $class): void
    {
        if (!str_starts_with($class, $this->namespace)) {
            return;
        }

        $relative = substr($class, strlen($this->namespace));

        $file = $this->basePath . DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $relative) .
            '.php';

        if (is_file($file)) {
            require_once $file;
        }
    }
}