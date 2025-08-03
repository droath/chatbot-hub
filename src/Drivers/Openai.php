<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use OpenAI\Client;
use Illuminate\Support\Str;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Resources\OpenaiChatResource;
use Droath\ChatbotHub\Resources\OpenaiEmbeddingResource;
use Droath\ChatbotHub\Resources\OpenaiResponsesResource;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasEmbeddingInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasResponsesInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface;

/**
 * Define the OpenAI driver for chatbot hub.
 */
class Openai extends ChatbotHubDriver implements HasChatInterface, HasResponsesInterface, HasEmbeddingInterface
{
    /** @var string */
    public const string DEFAULT_MODEL = 'gpt-4o-mini';

    /** @var string */
    public const string DEFAULT_EMBEDDING_MODEL = 'text-embedding-3-small';

    /**
     * @param \OpenAI\Client $client
     */
    public function __construct(
        protected Client $client
    ) {}

    /**
     * @inheritDoc
     */
    public static function transformTool(Tool $tool): array
    {
        $data = $tool->toArray();

        $definition = [
            'type' => 'function',
            'name' => $data['name'],
            'strict' => $data['strict'] ?? false,
        ];

        if ($tool->hasProperties()) {
            $definition['parameters'] = [
                'type' => 'object',
                'properties' => $data['properties']
                    ->flatMap(function (ToolProperty $property) {
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
                    })->toArray(),
                'required' => $data['required'] ?? [],
            ];
        }

        return $definition;
    }

    /**
     * @inheritDoc
     */
    public static function transformUserMessage(string $content): string|array
    {
        if (Str::isJson($content)) {
            try {
                $contents = [];
                $parts = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

                foreach ($parts as $value) {
                    if (Str::startsWith($value, 'data:text')) {
                        $contents[] = [
                            'type' => 'text',
                            'text' => static::decodeBase64DataUri($value),
                        ];
                    }

                    if (Str::startsWith($value, 'data:image')) {
                        $contents[] = [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $value,
                            ],
                        ];
                    }

                    if (Str::startsWith($value, ['data:application'])) {
                        $contents[] = [
                            'type' => 'file',
                            'file' => [
                                'file_data' => $value,
                            ]
                        ];
                    }
                }
            } catch (\JsonException) {
                return [];
            }

            return $contents;
        }

        return $content;
    }

    /**
     * @param string $uri
     *
     * @return string|null
     */
    protected static function decodeBase64DataUri(string $uri): ?string
    {
        preg_match('/^data:([^;]+);base64,(.+)$/i', $uri, $matches);

        array_shift($matches);

        if (! empty($matches)) {
            [$mimeType, $data] = $matches;
            return base64_decode($data);
        }

        return null;
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
    public function responses(): ResponsesResourceInterface
    {
        return new OpenaiResponsesResource(
            $this->client->responses(),
            $this
        );
    }

    /**
     * {@inheritDoc}
     */
    public function embeddings(): EmbeddingsResourceInterface
    {
        return new OpenaiEmbeddingResource(
            $this->client->embeddings(),
            $this
        );
    }
}
