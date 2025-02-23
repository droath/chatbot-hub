<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use OpenAI\Client;
use OpenAI\Resources\Embeddings;
use Droath\ChatbotHub\Tools\Tool;
use Illuminate\Support\Collection;
use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Resources\OpenaiChatResource;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasEmbeddingInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

/**
 * Define the openai driver for chatbot hub.
 */
class Openai extends ChatbotHubDriver implements HasChatInterface, HasEmbeddingInterface
{
    public const string DEFAULT_MODEL = 'gpt-4o-mini';

    public function __construct(
        protected Client $client
    ) {}

    /**
     * @inheritDoc
     */
    public static function transformTool(Tool $tool): array
    {
        $data = $tool->toArray();

        return [
            'type' => 'function',
            'function' => array_filter([
                'name' => $data['name'],
                'strict' => $data['strict'] ?? false,
                'parameters' => $tool->hasProperties() ? [
                    'type' => 'object',
                    'properties' => static::transformToolProperties($data['properties']),
                    'required' => $data['required'] ?? [],
                ] : [],
            ]),
        ];
    }

    protected static function transformToolProperties(
        Collection $properties
    ): array
    {
        return $properties->flatMap(function (ToolProperty $property) {
            $data = $property->toArray();

            if ($name = $data['name']) {
                return [
                    $name => array_filter([
                        'type' => $data['type'],
                        'enum' => $data['enum'],
                        'description' => $data['description'],
                    ]),
                ];
            }

            return [];
        })->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function chat(): ChatResourceInterface
    {
        return new OpenaiChatResource(
            $this->client->chat(),
            $this
        );
    }

    /**
     * {@inheritDoc}
     */
    public function embeddings(): Embeddings
    {
        return $this->client->embeddings();
    }
}
