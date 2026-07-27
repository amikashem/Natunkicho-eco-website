<?php

declare(strict_types=1);

namespace NKRecruitment\AI\Services;

use NKRecruitment\Database\DatabaseManager;
use NKRecruitment\AI\Prompts\PromptLibrary;

if (!defined('ABSPATH')) {
    exit;
}

class AIGatewayService
{
    private \wpdb $db;
    private array $providers = [];

    public function __construct()
    {
        $this->db = DatabaseManager::db();
        $this->loadProviders();
    }

    /**
     * Loads the providers in the EXACT ORDER we want to attempt them.
     * Free Tiers first, Paid Tiers as fallbacks.
     */
    private function loadProviders(): void
    {
        // 1. GITHUB FREE TIER (Priority 1)
        $github_key = defined('nkjp_github_key') ? nkjp_github_key : get_option('nkrp_github_key', '');
        if (!empty($github_key)) {
            $github = new \NKRecruitment\AI\Providers\GitHubProvider('gpt-4o-mini');
            $github->setApiKey($github_key);
            $this->providers['github'] = $github;
        }

        // 2. GROK FREE TIER (Priority 2)
        $grok_key = defined('nkjp_grok_key') ? nkjp_grok_key : get_option('nkrp_grok_key', '');
        if (!empty($grok_key)) {
            $grok = new \NKRecruitment\AI\Providers\GrokProvider('grok-beta');
            $grok->setApiKey($grok_key);
            $this->providers['grok'] = $grok;
        }

        // 3. GOOGLE GEMINI (Priority 3)
        $gemini_key = defined('nkjp_gemini_key') ? nkjp_gemini_key : get_option('nkrp_gemini_key', '');
        if (!empty($gemini_key)) {
            $gemini = new \NKRecruitment\AI\Providers\GeminiProvider('gemini-1.5-flash');
            $gemini->setApiKey($gemini_key);
            $this->providers['gemini'] = $gemini;
        }

        // 4. OPENAI PAID TIER (Priority 4 - Ultimate Fallback)
        $openai_key = defined('nkjp_openai_key') ? nkjp_openai_key : get_option('nkrp_openai_key', '');
        if (!empty($openai_key)) {
            $openai = new \NKRecruitment\AI\Providers\OpenAIProvider('gpt-4o-mini');
            $openai->setApiKey($openai_key);
            $this->providers['openai'] = $openai;
        }
    }

    /**
     * The Waterfall Router: Loops through providers until one succeeds.
     */
    public function requestAI(string $module, string $action, string $userData, int $user_id = 0): string
    {
        if (empty($this->providers)) {
            return "Error: No AI Providers configured in the system.";
        }

        $systemPrompt = PromptLibrary::getSystemPrompt($action);
        $last_error = "";

        // WATERFALL LOOP: Tries GitHub -> Grok -> Gemini -> OpenAI
        foreach ($this->providers as $providerName => $providerInstance) {
            
            $response = $providerInstance->generate($systemPrompt, $userData);

            // If it succeeds, log the telemetry and break the loop!
            if ($response['success']) {
                $this->logTelemetry($user_id, $module, $action, $providerName, $response);
                return $response['content'];
            }

            // If it fails (Rate Limit, Network Error), save the error and let the loop try the next provider
            $last_error = $response['content'];
        }

        // If ALL providers failed, return the final error.
        return "AI Generation Failed across all providers. Last Error: " . $last_error;
    }

    private function logTelemetry(int $user_id, string $module, string $action, string $providerName, array $response): void
    {
        // Estimate costs (Free tiers log as $0.00)
        $cost = 0.00;
        if ($providerName === 'openai') {
            $cost = ($response['prompt_tokens'] * (0.150 / 1000000)) + ($response['completion_tokens'] * (0.600 / 1000000));
        }

        $table = DatabaseManager::table('ai_logs');
        $this->db->insert($table, [
            'user_id'           => $user_id,
            'module'            => sanitize_text_field($module),
            'action'            => sanitize_text_field($action),
            'provider'          => $providerName,
            'model_used'        => sanitize_text_field($response['model']),
            'prompt_tokens'     => (int) $response['prompt_tokens'],
            'completion_tokens' => (int) $response['completion_tokens'],
            'total_tokens'      => (int) $response['total_tokens'],
            'estimated_cost'    => $cost,
            'created_at'        => current_time('mysql')
        ]);
    }
}