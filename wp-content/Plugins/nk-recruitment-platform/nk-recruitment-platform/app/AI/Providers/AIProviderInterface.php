<?php

declare(strict_types=1);

namespace NKRecruitment\AI\Providers;

if (!defined('ABSPATH')) {
    exit;
}

interface AIProviderInterface
{
    /**
     * Set the API key for the provider.
     */
    public function setApiKey(string $apiKey): void;

    /**
     * Send a prompt to the AI and return a standardized response array.
     * * Expected return format:
     * [
     * 'content'           => 'The AI generated text...',
     * 'prompt_tokens'     => 150,
     * 'completion_tokens' => 200,
     * 'total_tokens'      => 350,
     * 'model'             => 'gpt-4o-mini'
     * ]
     */
    public function generate(string $systemPrompt, string $userPrompt): array;
}