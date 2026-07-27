<?php
namespace NK_AI_RankMath\AI;

use NK_AI_RankMath\Helpers\Cache;
use NK_AI_RankMath\Helpers\Logger;

class Gateway {
    private static $instance = null;
    private $cache = null;
    private $logger = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->cache = Cache::get_instance();
        $this->logger = Logger::get_instance();
    }
    
    public function init() {
        // Initialize any gateway setup
    }
    
    /**
     * Main gateway method - all AI features must call this
     * 
     * @param string $module The module name (e.g., 'seo_title', 'meta_description')
     * @param string $prompt_key The prompt template key
     * @param array|string $content The content to process
     * @param array $context Additional context (post_id, language, etc.)
     * @return array Response with 'success' and 'result' keys
     */
    public static function run($module, $prompt_key, $content, $context = []) {
        $instance = self::get_instance();
        return $instance->process($module, $prompt_key, $content, $context);
    }
    
    private function process($module, $prompt_key, $content, $context) {
        try {
            // Validate inputs
            if (empty($module) || empty($prompt_key) || empty($content)) {
                return $this->error_response('Missing required parameters');
            }
            
            // Check cache
            $cache_key = $this->generate_cache_key($module, $prompt_key, $content, $context);
            $cached = $this->cache->get($cache_key);
            if ($cached !== false) {
                return $this->success_response($cached);
            }
            
            // Build prompt
            $prompt = Prompt_Templates::build($prompt_key, $content, $context);
            if (!$prompt) {
                return $this->error_response('Invalid prompt template');
            }
            
            // Prepare request to Core API
            $request_data = [
                'module' => $module,
                'prompt' => $prompt,
                'content' => $content,
                'context' => $context,
                'timestamp' => time(),
                'site_url' => get_site_url(),
                'user_id' => get_current_user_id(),
            ];
            
            // Send to Core API
            $response = $this->send_to_core($request_data);
            
            if (!$response || !isset($response['success']) || !$response['success']) {
                return $this->error_response(
                    isset($response['error']) ? $response['error'] : 'Core API request failed'
                );
            }
            
            // Process and cache result
            $result = $response['result'] ?? '';
            $this->cache->set($cache_key, $result, HOUR_IN_SECONDS * 24);
            
            // Log success
            $this->logger->log('success', "AI processing successful for {$module}", [
                'module' => $module,
                'prompt_key' => $prompt_key
            ]);
            
            return $this->success_response($result);
            
        } catch (\Exception $e) {
            $this->logger->log('error', "AI processing failed", [
                'module' => $module,
                'error' => $e->getMessage()
            ]);
            return $this->error_response($e->getMessage());
        }
    }
    
    private function send_to_core($data) {
        // Send to NK Core API
        $api_url = defined('NK_AI_CORE_URL') ? NK_AI_CORE_URL : 'https://api.nkgroup.com/ai/v1/process';
        $api_key = defined('NK_AI_CORE_API_KEY') ? NK_AI_CORE_API_KEY : '';
        
        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
                'X-Plugin' => 'nk-ai-rankmath',
                'X-Version' => NK_AI_RANKMATH_VERSION,
            ],
            'body' => json_encode($data),
            'timeout' => 30,
        ];
        
        $response = wp_remote_post($api_url, $args);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    private function generate_cache_key($module, $prompt_key, $content, $context) {
        return md5($module . $prompt_key . serialize($content) . serialize($context));
    }
    
    private function success_response($data) {
        return ['success' => true, 'result' => $data];
    }
    
    private function error_response($message) {
        return ['success' => false, 'error' => $message];
    }
}