<?php
namespace NK_AI_RankMath\Helpers;

class Logger {
    private static $instance = null;
    private $enabled = true;
    private $log_file = '';
    private $min_level = 'info';
    
    private $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }
    
    private function init() {
        $this->log_file = WP_CONTENT_DIR . '/logs/nk-ai-rankmath.log';
        
        // Create log directory if it doesn't exist
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }
    
    public function log($level, $message, $context = []) {
        if (!$this->enabled) {
            return;
        }
        
        $level = strtolower($level);
        if (!isset($this->levels[$level])) {
            $level = 'info';
        }
        
        if ($this->levels[$level] < $this->levels[$this->min_level]) {
            return;
        }
        
        $timestamp = current_time('mysql');
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $log_entry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'user_id' => $user_id,
            'ip' => $ip,
            'message' => $message,
            'context' => $context,
            'version' => NK_AI_RANKMATH_VERSION,
        ];
        
        $log_line = json_encode($log_entry) . "\n";
        
        // Write to file
        file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Also log to error log if critical
        if ($level === 'critical') {
            error_log("NK AI RankMath: {$message}");
        }
    }
    
    public function debug($message, $context = []) {
        $this->log('debug', $message, $context);
    }
    
    public function info($message, $context = []) {
        $this->log('info', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('warning', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('error', $message, $context);
    }
    
    public function critical($message, $context = []) {
        $this->log('critical', $message, $context);
    }
    
    public function get_logs($limit = 100, $level = null) {
        if (!file_exists($this->log_file)) {
            return [];
        }
        
        $logs = [];
        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines);
        $count = 0;
        
        foreach ($lines as $line) {
            if ($count >= $limit) {
                break;
            }
            
            $entry = json_decode($line, true);
            if ($entry && (!$level || $entry['level'] === strtoupper($level))) {
                $logs[] = $entry;
                $count++;
            }
        }
        
        return $logs;
    }
    
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
            return true;
        }
        return false;
    }
}