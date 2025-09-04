<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use Anthropic\Contracts\ClientContract;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Resources\ClaudeChatResource;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

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

        return [
            'name' => $data['name'],
            'description' => $data['description'],
            'input_schema' => [
                'type' => 'object',
                'properties' => $data['parameters']['properties'] ?? [],
                'required' => $data['parameters']['required'] ?? [],
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
        return new ClaudeChatResource($this->client);
    }

    /**
     * Get the Claude client instance.
     */
    public function getClient(): ClientContract
    {
        return $this->client;
    }
}