<?php
namespace NK_AI_RankMath\API;

class REST {
    public static function register_routes() {
        register_rest_route('nk-ai-rankmath/v1', '/generate', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_generate'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
        
        register_rest_route('nk-ai-rankmath/v1', '/bulk', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_bulk'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
    }
    
    public static function check_permission() {
        return current_user_can('edit_posts');
    }
    
    public static function handle_generate($request) {
        $params = $request->get_params();
        $type = $params['type'] ?? '';
        $content = $params['content'] ?? '';
        $context = $params['context'] ?? [];
        
        if (empty($type) || empty($content)) {
            return new \WP_REST_Response([
                'success' => false,
                'error' => 'Missing required parameters'
            ], 400);
        }
        
        // Route to appropriate handler
        $result = \NK_AI_RankMath\AI\Gateway::run($type, $type, $content, $context);
        
        return new \WP_REST_Response($result, 200);
    }
    
    public static function handle_bulk($request) {
        // Handle bulk operations
        return new \WP_REST_Response(['success' => true], 200);
    }
}