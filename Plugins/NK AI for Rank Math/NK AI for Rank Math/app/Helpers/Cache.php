<?php
namespace NK_AI_RankMath\Helpers;

class Cache {
    private static $instance = null;
    private $group = 'nk_ai_rankmath';
    private $enabled = true;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key) {
        if (!$this->enabled) {
            return false;
        }
        
        $value = wp_cache_get($key, $this->group);
        if ($value !== false) {
            return $value;
        }
        
        // Check alternative storage (transient)
        $transient_key = $this->get_transient_key($key);
        $value = get_transient($transient_key);
        if ($value !== false) {
            // Store in object cache for faster future access
            wp_cache_set($key, $value, $this->group, HOUR_IN_SECONDS);
            return $value;
        }
        
        return false;
    }
    
    public function set($key, $value, $expiration = HOUR_IN_SECONDS) {
        if (!$this->enabled) {
            return false;
        }
        
        wp_cache_set($key, $value, $this->group, $expiration);
        $transient_key = $this->get_transient_key($key);
        set_transient($transient_key, $value, $expiration);
        
        return true;
    }
    
    public function delete($key) {
        wp_cache_delete($key, $this->group);
        $transient_key = $this->get_transient_key($key);
        delete_transient($transient_key);
        
        return true;
    }
    
    public function clear() {
        wp_cache_flush();
        
        // Clear all related transients
        global $wpdb;
        $like = '_transient_' . $this->group . '_%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                $like
            )
        );
        
        return true;
    }
    
    private function get_transient_key($key) {
        return "{$this->group}_{$key}";
    }
    
    public function enable() {
        $this->enabled = true;
    }
    
    public function disable() {
        $this->enabled = false;
    }
}