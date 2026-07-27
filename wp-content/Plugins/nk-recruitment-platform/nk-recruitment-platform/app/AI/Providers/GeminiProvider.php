<?php

declare(strict_types=1);

namespace NKRecruitment\AI\Providers;

if (!defined('ABSPATH')) {
    exit;
}

class GeminiProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct(string $model = 'gemini-1.5-flash')
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
            return $this->errorResponse('Gemini API Key is missing.');
        }

        $endpoint = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;

        // Gemini payload structure
        $body = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => "System Instructions: " . $systemPrompt . "\n\nUser Request: " . $userPrompt]
                    ]
                ]
            ]
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($body),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $this->errorResponse($response->get_error_message());
        }

        $responseBody = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($responseBody['error'])) {
            return $this->errorResponse($responseBody['error']['message'] ?? 'Unknown Gemini Error');
        }

        // Standardize output to match the Gateway Interface perfectly
        return [
            'success'           => true,
            'content'           => $responseBody['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'prompt_tokens'     => $responseBody['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $responseBody['usageMetadata']['candidatesTokenCount'] ?? 0,
            'total_tokens'      => $responseBody['usageMetadata']['totalTokenCount'] ?? 0,
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