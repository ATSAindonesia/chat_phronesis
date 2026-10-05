<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TokenportalService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $defaultModel;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tokenportal.api_key');
        $this->baseUrl = rtrim((string) config('services.tokenportal.base_url', 'https://tokenportal.id/v1'), '/');
        $this->defaultModel = (string) config('services.tokenportal.default_model', 'kimi-k27-code');
        $this->timeout = (int) config('services.tokenportal.timeout', 90);
    }

    /**
     * Get available models from Tokenportal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getModels(bool $forceRefresh = false): array
    {
        $cacheKey = 'tokenportal_models_list';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(15)
                    ->acceptJson()
                    ->get("{$this->baseUrl}/models");

                if ($response->successful()) {
                    $json = $response->json();
                    $rawModels = $json['data'] ?? [];

                    if (! empty($rawModels) && is_array($rawModels)) {
                        return array_map(function ($m) {
                            $id = $m['id'] ?? '';
                            $ownedBy = $m['owned_by'] ?? 'Tokenportal';
                            $contextWindow = $m['context_window'] ?? $m['context_length'] ?? null;
                            $maxTokens = $m['max_tokens'] ?? $m['max_output_tokens'] ?? null;

                            return [
                                'id' => $id,
                                'name' => $this->formatModelName($id),
                                'owned_by' => $ownedBy,
                                'context_window' => $contextWindow,
                                'max_tokens' => $maxTokens,
                                'description' => $this->getModelDescription($id, $ownedBy),
                                'supports_reasoning' => str_contains($id, 'kimi') || str_contains($id, 'reasoning') || str_contains($id, 'preview'),
                            ];
                        }, $rawModels);
                    }
                }

                Log::warning('Tokenportal API getModels failed with status: '.$response->status(), [
                    'body' => $response->body(),
                ]);
            } catch (Exception $e) {
                Log::error('Tokenportal API getModels exception: '.$e->getMessage());
            }

            return $this->getFallbackModels();
        });
    }

    /**
     * Send a chat completion request to Tokenportal.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{
     *     id: string|null,
     *     model: string,
     *     role: string,
     *     content: string,
     *     reasoning: string|null,
     *     tokens: int|null,
     *     usage: array<string, mixed>,
     *     finish_reason: string|null,
     *     raw: array<string, mixed>
     * }
     */
    public function chatCompletion(array $messages, ?string $model = null, array $options = []): array
    {
        $chosenModel = $model ?: $this->defaultModel;

        $formattedMessages = array_values(array_map(function ($msg) {
            return [
                'role' => (string) ($msg['role'] ?? 'user'),
                'content' => (string) ($msg['content'] ?? ''),
            ];
        }, $messages));

        $payload = [
            'model' => $chosenModel,
            'messages' => $formattedMessages,
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = (float) $options['temperature'];
        }

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = (int) $options['max_tokens'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/completions", $payload);

            if (! $response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $errorData['message'] ?? $response->body() ?: 'Tokenportal API returned status '.$response->status();

                Log::error('Tokenportal Chat Completion error', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'model' => $chosenModel,
                ]);

                throw new RuntimeException("Tokenportal Error ({$response->status()}): {$errorMessage}");
            }

            $data = $response->json();
            $choice = $data['choices'][0] ?? [];
            $choiceMessage = $choice['message'] ?? [];

            $content = (string) ($choiceMessage['content'] ?? '');
            $reasoning = isset($choiceMessage['reasoning']) && $choiceMessage['reasoning'] !== ''
                ? (string) $choiceMessage['reasoning']
                : null;

            $usage = $data['usage'] ?? [];
            $tokens = $usage['total_tokens'] ?? $usage['completion_tokens'] ?? null;

            return [
                'id' => $data['id'] ?? null,
                'model' => $data['model'] ?? $chosenModel,
                'role' => $choiceMessage['role'] ?? 'assistant',
                'content' => $content,
                'reasoning' => $reasoning,
                'tokens' => $tokens ? (int) $tokens : null,
                'usage' => $usage,
                'finish_reason' => $choice['finish_reason'] ?? null,
                'raw' => $data,
            ];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Tokenportal Chat Completion exception', [
                'error' => $e->getMessage(),
                'model' => $chosenModel,
            ]);

            throw new RuntimeException('Failed to communicate with Tokenportal: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Format a model identifier to a readable label.
     */
    public function formatModelName(string $id): string
    {
        $customNames = [
            'kimi-k27-code' => 'Kimi K2.7 Code',
            'kimi-k27-code-fast' => 'Kimi K2.7 Code Fast',
            'kimi-k3' => 'Kimi K3',
            'kimi-k3-fast' => 'Kimi K3 Fast',
            'qwen-35b-fast' => 'Qwen 35B Fast',
            'qwen-35b' => 'Qwen 35B',
            'qwen-3.8-27b' => 'Qwen 3.8 27B',
            'deepseek-v4-flash' => 'DeepSeek V4 Flash',
            'deepseek-v4.1-flash' => 'DeepSeek V4.1 Flash',
            'deepseek-v4-pro' => 'DeepSeek V4 Pro',
            'deepseek-v4-flash-speed' => 'DeepSeek V4 Flash Speed',
            'deepseek-v4-flash-unli' => 'DeepSeek V4 Flash Unlimited',
            'deepseek-v4.1-flash-unli' => 'DeepSeek V4.1 Flash Unlimited',
            'gpt-4o' => 'GPT-4o',
            'gpt-5.5' => 'GPT-5.5',
            'gpt-5.4' => 'GPT-5.4',
            'gpt-5.6-sol' => 'GPT-5.6 Sol',
            'gpt-5.6-luna' => 'GPT-5.6 Luna',
            'gpt-5.6-terra' => 'GPT-5.6 Terra',
            'gpt-6-sol' => 'GPT-6 Sol',
            'gpt-6-luna' => 'GPT-6 Luna',
            'gpt-6-astra' => 'GPT-6 Astra',
            'gpt-6.1-sol' => 'GPT-6.1 Sol',
            'claude-sonnet-5' => 'Claude Sonnet 5',
            'claude-sonnet-5-5' => 'Claude Sonnet 5.5',
            'claude-sonnet-4-6' => 'Claude Sonnet 4.6',
            'claude-sonnet-4-5-20250929' => 'Claude Sonnet 4.5',
            'claude-opus-4-6' => 'Claude Opus 4.6',
            'claude-opus-4-7' => 'Claude Opus 4.7',
            'claude-opus-4-8' => 'Claude Opus 4.8',
            'claude-opus-5' => 'Claude Opus 5',
            'claude-opus-5-5' => 'Claude Opus 5.5',
            'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5',
            'gemini-3.8-flash' => 'Gemini 3.8 Flash',
            'gemma-31b' => 'Gemma 31B',
            'glm-53' => 'GLM 53',
            'glm-53-flash' => 'GLM 53 Flash',
            'nemotron-lightning' => 'Nemotron Lightning',
            'nemotron-ultra' => 'Nemotron Ultra',
            'nemotron-3.5' => 'Nemotron 3.5',
            'mimo-v26-flash' => 'Mimo V26 Flash',
            'grok-47' => 'Grok 4.7',
        ];

        if (isset($customNames[$id])) {
            return $customNames[$id];
        }

        // Auto uppercase and format dashes
        return ucwords(str_replace(['-', '_'], ' ', $id));
    }

    /**
     * Provide a brief description for common models.
     */
    protected function getModelDescription(string $id, string $ownedBy): string
    {
        if (str_contains($id, 'code')) {
            return "{$ownedBy} • Specialized for programming & logic";
        }
        if (str_contains($id, 'fast') || str_contains($id, 'flash')) {
            return "{$ownedBy} • Ultra-fast response speed";
        }
        if (str_contains($id, 'pro') || str_contains($id, 'opus')) {
            return "{$ownedBy} • High-intelligence & deep reasoning";
        }

        return "{$ownedBy} • High-performance AI model";
    }

    /**
     * Fallback models list in case Tokenportal endpoint is unreachable.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFallbackModels(): array
    {
        $defaults = [
            ['id' => 'kimi-k27-code', 'owned_by' => 'Moonshot', 'context_window' => 200000, 'max_tokens' => 64000],
            ['id' => 'kimi-k27-code-fast', 'owned_by' => 'Moonshot', 'context_window' => 200000, 'max_tokens' => 64000],
            ['id' => 'kimi-k3', 'owned_by' => 'Moonshot', 'context_window' => 200000, 'max_tokens' => 65536],
            ['id' => 'qwen-35b-fast', 'owned_by' => 'Alibaba', 'context_window' => 262128, 'max_tokens' => 65536],
            ['id' => 'deepseek-v4-flash', 'owned_by' => 'DeepSeek', 'context_window' => 1000000, 'max_tokens' => 8192],
            ['id' => 'deepseek-v4.1-flash', 'owned_by' => 'DeepSeek', 'context_window' => 1000000, 'max_tokens' => 8192],
            ['id' => 'gpt-4o', 'owned_by' => 'OpenAI', 'context_window' => 128000, 'max_tokens' => 32768],
            ['id' => 'claude-sonnet-5-5', 'owned_by' => 'Anthropic', 'context_window' => 1000000, 'max_tokens' => 65536],
            ['id' => 'gemini-3.8-flash', 'owned_by' => 'Google', 'context_window' => 1048576, 'max_tokens' => 8192],
        ];

        return array_map(function ($m) {
            $id = $m['id'];
            $ownedBy = $m['owned_by'];

            return [
                'id' => $id,
                'name' => $this->formatModelName($id),
                'owned_by' => $ownedBy,
                'context_window' => $m['context_window'],
                'max_tokens' => $m['max_tokens'],
                'description' => $this->getModelDescription($id, $ownedBy),
                'supports_reasoning' => str_contains($id, 'kimi'),
            ];
        }, $defaults);
    }
}
