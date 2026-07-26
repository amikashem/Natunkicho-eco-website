<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_AI_Gateway {

    /**
     * The single endpoint for ALL NatunKicho AI Requests (SEO, ATS, CV Builder)
     * Usage: NK_AI_Gateway::request( 'seo_module', 'System Prompt', 'User Prompt' );
     */
    public static function request( $module_name, $system_prompt, $user_prompt, $bypass_cache = false ) {
        global $wpdb;

        // 1. Generate a unique hash for this exact request
        $request_hash = md5( $module_name . $system_prompt . $user_prompt );
        $cache_table  = $wpdb->prefix . 'nk_ai_cache';

        // 2. Check Cache (Never pay for the same API response twice)
        if ( ! $bypass_cache ) {
            $cached_response = $wpdb->get_var( $wpdb->prepare(
                "SELECT response_data FROM {$cache_table} WHERE request_hash = %s",
                $request_hash
            ) );

            if ( $cached_response ) {
                return array(
                    'success' => true,
                    'data'    => $cached_response,
                    'source'  => 'cache'
                );
            }
        }

        // 3. Setup API Keys
        $openai_key = defined( 'nkjp_openai_key' ) ? nkjp_openai_key : '';
        $gemini_key = defined( 'nkjp_gemini_key' ) ? nkjp_gemini_key : ''; 

        if ( empty( $openai_key ) && empty( $gemini_key ) ) {
            return array( 'success' => false, 'error' => 'All API keys are missing in configuration.' );
        }

        // 4. Advanced Provider Routing Logic (Load Balancing & Limit Protection)
        $provider = null;
        $provider_name = 'openai';

        if ( in_array( $module_name, array( 'seo_module', 'content_module', 'translation' ), true ) ) {
            // Route heavy writing tasks to Gemini
            if ( ! empty( $gemini_key ) ) {
                $provider_name = 'gemini';
                require_once plugin_dir_path( __FILE__ ) . 'class-nk-ai-gemini.php';
                $provider = new NK_AI_Gemini( $gemini_key );
            }
        } 
        
        // Fallback to OpenAI if Gemini wasn't selected or didn't have a key
        if ( ! $provider && ! empty( $openai_key ) ) {
            $provider_name = 'openai';
            require_once plugin_dir_path( __FILE__ ) . 'class-nk-ai-openai.php';
            $provider = new NK_AI_OpenAI( $openai_key );
        }

        if ( ! $provider ) {
            return array( 'success' => false, 'error' => 'No suitable AI provider configured for module: ' . $module_name );
        }

        // 5. Execute Request
        $start_time = microtime( true );
        $result     = $provider->generate( $system_prompt, $user_prompt );
        $end_time   = microtime( true );
        $time_ms    = round( ( $end_time - $start_time ) * 1000 );

       
       // 6. Handle Error (Temporarily debug raw error)
        if ( ! $result['success'] ) {
            error_log("AI GATEWAY RAW ERROR: " . print_r($result, true));
            return array( 'success' => false, 'error' => 'API Error: ' . $result['error'], 'source' => 'api' );
        }

        // 7. Save to Cache for future requests
        $wpdb->insert(
            $cache_table,
            array(
                'request_hash'  => $request_hash,
                'provider'      => $provider_name,
                'response_data' => $result['data'],
            ),
            array( '%s', '%s', '%s' )
        );

        // 8. Log the Usage and Cost
        $tokens_used = isset( $result['tokens_used'] ) ? (int)$result['tokens_used'] : 0;
        self::log_usage( $module_name, $provider_name, $tokens_used, $time_ms );

        return array(
            'success' => true,
            'data'    => $result['data'],
            'source'  => 'api'
        );
    }

    /**
     * Logs the token usage and estimates cost.
     */
    private static function log_usage( $module_name, $provider, $tokens, $time_ms ) {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'nk_ai_logs';
        $user_id    = get_current_user_id();
        
        // Cost estimation roughly based on standard tiering
        $estimated_cost = ( $tokens / 1000 ) * 0.0015;

        $wpdb->insert(
            $logs_table,
            array(
                'user_id'          => $user_id,
                'module_name'      => $module_name,
                'provider'         => $provider,
                'tokens_used'      => $tokens,
                'estimated_cost'   => $estimated_cost,
                'response_time_ms' => $time_ms
            ),
            array( '%d', '%s', '%s', '%d', '%f', '%d' )
        );
    }
}