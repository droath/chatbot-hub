<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources;

use Anthropic\Contracts\ClientContract;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Responses\Messages\StreamResponse;
use Anthropic\Responses\Messages\CreateStreamedResponseDelta;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Resources\Concerns\WithMessages;
use Droath\ChatbotHub\Resources\Concerns\WithModel;
use Droath\ChatbotHub\Resources\Concerns\WithResponseFormat;
use Droath\ChatbotHub\Resources\Concerns\WithTools;
use Droath\ChatbotHub\Drivers\Concerns\HasStreaming;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasDriverInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolTransformerInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasStreamingInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Define the Claude chat resource.
 */
class ClaudeChatResource implements ChatResourceInterface, HasDriverInterface, HasMessagesInterface, HasResponseFormatInterface, HasStreamingInterface, HasToolsInterface, HasToolTransformerInterface
{
    use HasStreaming;
    use WithMessages;
    use WithModel;
    use WithResponseFormat;
    use WithTools;

    protected string $model = Claude::DEFAULT_MODEL;

    public function __construct(
        protected ClientContract $client,
        protected DriverInterface $driver
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
     * Get all supported Claude models.
     */
    public static function getSupportedModels(): array
    {
        return [
            'claude-3-5-sonnet-20241022',
            'claude-3-5-sonnet-20240620',
            'claude-3-5-haiku-20241022',
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
        ];
    }

    /**
     * Validate if a model is supported.
     */
    public static function isModelSupported(string $model): bool
    {
        return in_array($model, self::getSupportedModels(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(): ?ChatbotHubResponseMessage
    {
        $parameters = $this->resourceParameters();

        // Return null if no messages to send
        if (empty($parameters)) {
            return null;
        }

        return $this->handleResponse(
            $this->createResourceResponse($parameters)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function driver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * Create the chat resource response.
     */
    protected function createResourceResponse(
        array $parameters
    ): StreamResponse|CreateResponse {
        return ! $this->stream
            ? $this->client->messages()->create($parameters)
            : $this->client->messages()->createStreamed($parameters);
    }

    /**
     * Handle all chat responses.
     */
    protected function handleResponse(object $response): ?ChatbotHubResponseMessage
    {
        try {
            return match (true) {
                $response instanceof StreamResponse => $this->handleStream($response),
                $response instanceof CreateResponse => $this->handleSynchronous($response),
                default => throw new \RuntimeException('Unexpected response type')
            };
        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Handle the chat synchronous process.
     */
    protected function handleSynchronous(
        CreateResponse $response
    ): ?ChatbotHubResponseMessage {
        // Handle tool calls if present
        if ($this->hasToolUse($response)) {
            return $this->handleToolUse($response);
        }

        return $this->processResponse($response);
    }

    /**
     * Handle the chat stream process.
     */
    protected function handleStream(
        StreamResponse $stream
    ): ?ChatbotHubResponseMessage {
        $toolUseBlocks = [];
        $streamContent = null;

        foreach ($stream as $response) {
            if ($response->type === 'content_block_delta') {
                $delta = $response->delta;

                if (isset($delta->partial_json)) {
                    $toolUseBlocks = $this->processStreamToolUse(
                        $response,
                        $toolUseBlocks
                    );
                }

                if (isset($delta->text)) {
                    $streamContent = $this->processStreamContent(
                        $delta->text,
                        $streamContent
                    );
                }
            }

            if ($response->type === 'message_stop') {
                if (! empty($toolUseBlocks)) {
                    return $this->executeToolCalls($toolUseBlocks);
                }

                $streamResponse = ChatbotHubResponseMessage::fromString(
                    $streamContent ?? ''
                );

                $streamFinished = $this->streamFinished;
                if (is_callable($streamFinished)) {
                    $streamFinished($streamResponse);
                }

                return $streamResponse;
            }
        }

        return null;
    }

    /**
     * Process the stream content.
     */
    protected function processStreamContent(
        string $chunk,
        ?string $streamContent
    ): ?string {
        if ($chunk) {
            $processorMethod = $this->useStreamBuffer
                ? 'handleStreamBufferProcess'
                : 'handleStreamProcess';

            if (method_exists($this, $processorMethod)) {
                $this->$processorMethod(
                    $chunk,
                    $streamContent
                );
            }

            $streamContent .= $chunk;
        }

        return $streamContent;
    }

    /**
     * Handle the standard stream process.
     */
    protected function handleStreamProcess(
        string $chunk,
        ?string $streamContent
    ): void {
        $streamProcess = $this->streamProcess;

        if (is_callable($streamProcess)) {
            $partial = $chunk;
            $initialized = is_null($streamContent);

            $streamProcess(
                $partial,
                $initialized,
            );
        }
    }

    /**
     * Handle the stream buffer process.
     */
    protected function handleStreamBufferProcess(
        string $chunk,
        ?string $streamContent
    ): void {
        $streamBufferProcess = $this->streamBufferProcess;

        if (
            is_callable($streamBufferProcess)
            && $streamBufferProcess(
                $chunk,
                $this->streamBuffer
            )
        ) {
            $partial = $this->streamBuffer.$chunk;

            $this->handleStreamProcess(
                $partial,
                $streamContent
            );

            $this->streamBuffer = null;
        } else {
            $this->streamBuffer .= $chunk;
        }
    }

    /**
     * Process stream tool use.
     */
    protected function processStreamToolUse(
        CreateStreamedResponseDelta $response,
        array $toolUseBlocks
    ): array {
        // Handle tool use streaming for Claude API
        // Implementation will depend on the specific streaming format
        return $toolUseBlocks;
    }

    /**
     * Check if response contains tool use.
     */
    protected function hasToolUse(CreateResponse $response): bool
    {
        return ! empty($response->content) &&
            collect($response->content)->contains(fn ($content) => $content->type === 'tool_use');
    }

    /**
     * Handle tool use in the response.
     */
    protected function handleToolUse(CreateResponse $response): ?ChatbotHubResponseMessage
    {
        $toolUseBlocks = collect($response->content)
            ->filter(fn ($content) => $content->type === 'tool_use')
            ->map(fn ($content) => $content->toArray())
            ->toArray();

        return $this->executeToolCalls($toolUseBlocks);
    }

    /**
     * Execute tool calls and get response.
     */
    protected function executeToolCalls(array $toolUseBlocks): ?ChatbotHubResponseMessage
    {
        if (empty($this->tools)) {
            return null;
        }

        $parameters = $this->resourceParameters();

        // Add the assistant's tool use to the conversation
        $parameters['messages'][] = [
            'role' => ChatbotRoles::ASSISTANT->value,
            'content' => array_merge(
                $this->getTextContent($parameters['messages']),
                $toolUseBlocks
            ),
        ];

        // Execute each tool and add results
        foreach ($toolUseBlocks as $toolUse) {
            $result = $this->invokeTool($toolUse);

            $parameters['messages'][] = [
                'role' => ChatbotRoles::USER->value,
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUse['id'],
                        'content' => $result,
                    ],
                ],
            ];
        }

        // Make another request with tool results
        $response = $this->createResourceResponse($parameters);

        return $this->handleResponse($response);
    }

    /**
     * Get text content from last message.
     */
    protected function getTextContent(array $messages): array
    {
        $lastMessage = end($messages);

        if (isset($lastMessage['content']) && is_array($lastMessage['content'])) {
            return collect($lastMessage['content'])
                ->where('type', 'text')
                ->toArray();
        }

        return [];
    }

    /**
     * Invoke a tool call.
     */
    protected function invokeTool(array $toolUse): ?string
    {
        $tool = $this->tools->firstWhere('name', $toolUse['name']);

        if ($tool instanceof Tool) {
            $arguments = $toolUse['input'] ?? [];

            return call_user_func_array($tool, $arguments);
        }

        return null;
    }

    /**
     * Handle different types of exceptions from the Claude API
     */
    protected function handleException(\Throwable $exception): ?ChatbotHubResponseMessage
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();

        // Handle rate limiting (429 status code)
        if ($code === 429 || str_contains($message, 'rate limit')) {
            Log::warning('Claude API rate limit exceeded: '.$message);

            return null;
        }

        // Handle authentication errors (401 status code)
        if ($code === 401 || str_contains($message, 'unauthorized') || str_contains($message, 'invalid api key')) {
            Log::error('Claude API authentication error: '.$message);

            return null;
        }

        // Handle quota/billing errors (403 status code)
        if ($code === 403 || str_contains($message, 'quota') || str_contains($message, 'billing')) {
            Log::error('Claude API quota/billing error: '.$message);

            return null;
        }

        // Handle server errors (500+ status codes)
        if ($code >= 500) {
            Log::error('Claude API server error: '.$message);

            return null;
        }

        // Handle validation errors (400 status code)
        if ($code === 400 || str_contains($message, 'validation') || str_contains($message, 'invalid')) {
            Log::error('Claude API validation error: '.$message);

            return null;
        }

        // Log any other unexpected errors
        Log::error('Claude API unexpected error: '.$message, [
            'code' => $code,
            'exception' => get_class($exception),
        ]);

        return null;
    }

    /**
     * Process the Claude API response
     */
    protected function processResponse(CreateResponse $response): ?ChatbotHubResponseMessage
    {
        $content = $response->content[0]->text ?? null;

        if ($content === null) {
            return null;
        }

        return ChatbotHubResponseMessage::fromString($content);
    }

    /**
     * Define the Claude resource parameters
     */
    protected function resourceParameters(): array
    {
        $messages = $this->resolveMessages();

        // Validate that we have messages to send
        if (empty($messages)) {
            Log::warning('Claude API: No messages provided for request');

            return [];
        }

        // Claude requires system messages to be separate from the messages array
        $systemMessage = null;
        $userAssistantMessages = [];

        foreach ($messages as $message) {
            if ($message['role'] === ChatbotRoles::SYSTEM->value) {
                $systemMessage = $message['content'];
            } else {
                $userAssistantMessages[] = $message;
            }
        }

        $parameters = [
            'model' => $this->model,
            'messages' => $userAssistantMessages,
            'max_tokens' => 4096, // Claude requires max_tokens parameter
        ];

        if ($systemMessage !== null) {
            $parameters['system'] = $systemMessage;
        }

        if (! empty($this->resolveTools())) {
            $parameters['tools'] = $this->resolveTools();
        }

        if ($this->stream) {
            $parameters['stream'] = true;
        }

        return array_filter($parameters);
    }
}
