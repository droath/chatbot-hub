<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use Illuminate\Support\Collection;
use Anthropic\Contracts\ClientContract;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Resources\ClaudeChatResource;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Define the Claude driver for chatbot hub.
 */
class Claude extends ChatbotHubDriver implements HasChatInterface
{
    /** @var string */
    public const string DEFAULT_MODEL = 'claude-3-5-sonnet-20241022';

    public function __construct(
        protected ClientContract $client
    ) {}

    /**
     * {@inheritDoc}
     */
    public static function transformTool(Tool $tool): array
    {
        $data = $tool->toArray();

        $properties = [];
        if ($data['properties'] instanceof Collection) {
            foreach ($data['properties'] as $property) {
                $propData = $property->toArray();
                $properties[$propData['name']] = [
                    'type' => $propData['type'],
                    'description' => $propData['description'],
                ];

                if (! empty($propData['enum'])) {
                    $properties[$propData['name']]['enum'] = $propData['enum'];
                }
            }
        }

        return [
            'name' => $data['name'],
            'description' => $data['description'],
            'input_schema' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $data['required'] ?? [],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function transformUserMessage(string $content): string|array
    {
        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function chat(): ChatResourceInterface
    {
        return new ClaudeChatResource($this->client, $this);
    }

    /**
     * Get the Claude client instance.
     */
    public function getClient(): ClientContract
    {
        return $this->client;
    }

    /**
     * Validate the Claude configuration
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        // Check if API key is configured
        $apiKey = config('chatbot-hub.claude.api_key');
        if (empty($apiKey)) {
            $errors[] = 'Claude API key is not configured. Set ANTHROPIC_API_KEY environment variable.';
            Log::warning('Claude driver: API key not configured');
        } elseif (! $this->isValidApiKeyFormat($apiKey)) {
            $errors[] = 'Claude API key format is invalid. Should start with "sk-ant-".';
            Log::error('Claude driver: Invalid API key format provided');
        } else {
            Log::info('Claude driver: Configuration validation passed');
        }

        return $errors;
    }

    /**
     * Validate a model name format (basic validation only)
     */
    public function validateModel(string $model): array
    {
        $errors = [];

        if (empty($model)) {
            $errors[] = 'Model name cannot be empty.';
            Log::warning('Claude driver: Empty model name provided for validation');
        } elseif (! $this->isValidModelFormat($model)) {
            $errors[] = "Model '{$model}' has invalid format. Expected format like 'claude-3-sonnet-20240229'.";
            Log::warning("Claude driver: Invalid model format provided: {$model}");
        } else {
            Log::info("Claude driver: Model validation passed for: {$model}");
        }

        return $errors;
    }

    /**
     * Check if the API key has the correct format
     */
    protected function isValidApiKeyFormat(string $apiKey): bool
    {
        return str_starts_with($apiKey, 'sk-ant-') && strlen($apiKey) > 10;
    }

    /**
     * Check if model name has basic valid format
     */
    protected function isValidModelFormat(string $model): bool
    {
        // Basic format validation - should start with 'claude-' and have reasonable length
        return str_starts_with($model, 'claude-') && strlen($model) >= 10 && strlen($model) <= 50;
    }
}
