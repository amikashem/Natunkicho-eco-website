<?php
namespace NK_AI_RankMath\Core;

class Deactivator {
    public static function deactivate() {
        // Clear cache
        \NK_AI_RankMath\Helpers\Cache::get_instance()->clear();
        
        // Log deactivation
        \NK_AI_RankMath\Helpers\Logger::get_instance()->info('Plugin deactivated');
        
        // Remove scheduled cron jobs
        wp_clear_scheduled_hook('nk_ai_rankmath_cleanup');
    }
}