<?php
namespace NK_AI_RankMath\Admin;

class Admin {
    public function init() {
        // Initialize settings
        $settings = new Settings();
        $settings->init();
    }
}