<?php

declare(strict_types=1);

namespace NKRecruitment\AI\Providers;

if (!defined('ABSPATH')) {
    exit;
}

class GitHubProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://models.inference.ai.azure.com/chat/completions'; // GitHub Models Endpoint

    public function __construct(string $model = 'gpt-4o-mini') // GitHub provides standard models for free
    {
        $this->model = $model;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        if (empty($this->apiKey)) return $this->errorResponse('GitHub API Key missing.');

        $body = [
            'model'    => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ]
        ];

        $response = wp_remote_post($this->apiUrl, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $this->apiKey],
            'body'    => json_encode($body),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) return $this->errorResponse($response->get_error_message());

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($data['error'])) return $this->errorResponse($data['error']['message'] ?? 'GitHub API Error');

        return [
            'success'           => true,
            'content'           => $data['choices'][0]['message']['content'] ?? '',
            'prompt_tokens'     => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
            'total_tokens'      => $data['usage']['total_tokens'] ?? 0,
            'model'             => 'github-'.$this->model,
        ];
    }

    private function errorResponse(string $message): array
    {
        return ['success' => false, 'content' => $message, 'prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0, 'model' => 'github-'.$this->model];
    }
}