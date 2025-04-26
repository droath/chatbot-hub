<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use OpenAI\Client;
use Illuminate\Support\Str;
use OpenAI\Resources\Embeddings;
use Droath\ChatbotHub\Tools\Tool;
use Illuminate\Support\Collection;
use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Resources\OpenaiChatResource;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasEmbeddingInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

/**
 * Define the OpenAI driver for chatbot hub.
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
     * @param \Illuminate\Support\Collection $properties
     *
     * @return array
     */
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
