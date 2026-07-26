<?php

declare(strict_types=1);

namespace NKRecruitment\AI\Providers;

if (!defined('ABSPATH')) {
    exit;
}

class OpenAIProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct(string $model = 'gpt-4o-mini')
    {
        $this->model = $model;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        if (empty($this->apiKey)) {
            return $this->errorResponse('OpenAI API Key is missing.');
        }

        $body = [
            'model'       => $this->model,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.7,
        ];

        $response = wp_remote_post($this->apiUrl, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'body'    => json_encode($body),
            'timeout' => 30, // AI can sometimes take a moment to reply
        ]);

        if (is_wp_error($response)) {
            return $this->errorResponse($response->get_error_message());
        }

        $responseBody = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($responseBody['error'])) {
            return $this->errorResponse($responseBody['error']['message'] ?? 'Unknown OpenAI API Error');
        }

        // Standardize the output as required by the Interface
        return [
            'success'           => true,
            'content'           => $responseBody['choices'][0]['message']['content'] ?? '',
            'prompt_tokens'     => $responseBody['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $responseBody['usage']['completion_tokens'] ?? 0,
            'total_tokens'      => $responseBody['usage']['total_tokens'] ?? 0,
            'model'             => $this->model,
        ];
    }

    private function errorResponse(string $message): array
    {
        return [
            'success'           => false,
            'content'           => 'AI Generation Failed: ' . $message,
            'prompt_tokens'     => 0,
            'completion_tokens' => 0,
            'total_tokens'      => 0,
            'model'             => $this->model,
        ];
    }
}