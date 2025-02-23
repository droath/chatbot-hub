<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources;

use OpenAI\Resources\Chat;
use Illuminate\Support\Arr;
use Droath\ChatbotHub\Tools\Tool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenAI\Responses\StreamResponse;
use Droath\ChatbotHub\Drivers\Openai;
use Droath\ChatbotHub\Enums\ChatbotRoles;
use OpenAI\Responses\Chat\CreateResponse;
use Illuminate\Contracts\Support\Arrayable;
use OpenAI\Responses\Chat\CreateResponseChoice;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use Droath\ChatbotHub\Drivers\Concerns\HasStreaming;
use OpenAI\Responses\Chat\CreateStreamedResponseChoice;
use OpenAI\Responses\Chat\CreateStreamedResponseToolCall;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Drivers\Contracts\HasStreamingInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

/**
 * Define the openai chat resource.
 */
class OpenaiChatResource implements ChatResourceInterface, HasStreamingInterface
{
    protected array $responseFormat = [];

    protected array|Collection $tools = [];

    protected string $model = Openai::DEFAULT_MODEL;

    protected array|MessageStorageInterface $messages;

    use HasStreaming;

    public function __construct(Chat $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function withModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withTools(array $tools): static
    {
        $this->tools = collect($tools);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withResponseFormat(array $responseFormat): static
    {
        $this->responseFormat = $responseFormat;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMessages(array|MessageStorageInterface $messages): static
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * The openai chat resource invoke process.
     */
    public function __invoke(): ?ChatbotHubResponseMessage
    {
        $parameters = $this->resourceParameters();

        return $this->handleResponse(
            $this->createResourceResponse($parameters)
        );
    }

    /**
     * Create the chat resource response.
     */
    protected function createResourceResponse(
        array $parameters
    ): StreamResponse|CreateResponse
    {
        return ! $this->stream
            ? $this->resource->create($parameters)
            : $this->resource->createStreamed($parameters);
    }

    /**
     * Process the chat stream content.
     */
    protected function processStreamContent(
        CreateStreamedResponse $response,
        ?string $streamContent
    ): ?string
    {
        if ($chunk = $response->choices[0]->delta->content) {
            $processCallable = $this->streamProcess;

            if (is_callable($processCallable)) {
                $initialized = is_null($streamContent);

                $processCallable(
                    $chunk,
                    $initialized
                );
            }

            $streamContent .= $chunk;
        }

        return $streamContent;
    }

    /**
     * Define the openai response parameters.
     */
    protected function resourceParameters(): array
    {
        return array_filter([
            'model' => $this->model,
            'tools' => $this->tools instanceof Collection
                ? $this->tools->map(fn ($tool) => Openai::transformTool($tool))->toArray()
                : $this->tools,
            'messages' => $this->resolveMessages(),
            'response_format' => $this->responseFormat,
        ]);
    }

    /**
     * Resolve the chat messages.
     */
    protected function resolveMessages(): array
    {
        $messages = $this->messages;

        if (is_array($messages)) {
            foreach ($messages as &$message) {
                if ($message instanceof Arrayable) {
                    $message = $message->toArray();
                }
            }
        }

        if ($messages instanceof MessageStorageInterface) {
            $messages = $this->messages->toArray();
        }

        return array_filter($messages);
    }

    /**
     * Determine if the response is a tool call.
     */
    protected function isToolCall(CreateResponseChoice $choice): bool
    {
        return ($choice->finishReason === 'tool_calls')
            && ! empty($choice->message?->toolCalls ?? []);
    }

    /**
     * Invoke the chat tool from the response.
     *
     * @throws \JsonException
     */
    protected function invokeTool(
        CreateResponseToolCall|CreateStreamedResponseToolCall $toolCall
    ): ?string
    {
        $tool = $this->tools->firstWhere('name', $toolCall->function->name);

        if ($tool instanceof Tool) {
            $arguments = ! empty($toolCall->function->arguments)
                ? json_decode(
                    $toolCall->function->arguments,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ) : [];

            return call_user_func_array($tool, $arguments);
        }

        return null;
    }

    /**
     * Handle the chat tool calling.
     *
     * @throws \JsonException
     */
    protected function handleToolCall(
        CreateResponseChoice|CreateStreamedResponseChoice $choice
    ): CreateResponse|StreamResponse
    {
        $parameters = $this->resourceParameters();

        $choiceInstance = match (true) {
            $choice instanceof CreateResponseChoice => $choice->message,
            $choice instanceof CreateStreamedResponseChoice => $choice->delta
        };
        $parameters['messages'][] = $choiceInstance->toArray();

        foreach ($choiceInstance->toolCalls as $toolCall) {
            if ($toolCall->type !== 'function') {
                continue;
            }

            $parameters['messages'][] = [
                'role' => ChatbotRoles::TOOL->value,
                'content' => $this->invokeTool($toolCall),
                'tool_call_id' => $toolCall->id,
            ];
        }

        return $this->createResourceResponse($parameters);
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
            Log::error($exception->getMessage());

            return null;
        }
    }

    /**
     * Handle the chat synchronous process.
     *
     * @throws \JsonException
     */
    protected function handleSynchronous(
        CreateResponse $response
    ): ?ChatbotHubResponseMessage
    {
        foreach ($response->choices as $choice) {
            if (
                $this->isToolCall($choice)
                && $response = $this->handleToolCall($choice)
            ) {
                $this->handleResponse($response);
            }
        }

        return ChatbotHubResponseMessage::from($response->choices[0]);
    }

    /**
     * Process the stream tool calls.
     */
    protected function processStreamToolCalls(
        CreateStreamedResponse $response,
        array $streamToolCalls
    ): array
    {
        if (empty($this->tools)) {
            return $streamToolCalls;
        }

        foreach ($response->choices as $choice) {
            if (empty($choice->delta->toolCalls)) {
                continue;
            }
            foreach ($choice->toArray() as $parentKey => $value) {
                if (is_array($value)) {
                    foreach (Arr::dot($value) as $nestedKey => $nestedValue) {
                        $prevValue = Arr::get($streamToolCalls, "$parentKey.$nestedKey", '');
                        $prevValue .= $nestedValue;
                        Arr::set(
                            $streamToolCalls,
                            "$parentKey.$nestedKey",
                            $prevValue
                        );
                    }
                } else {
                    $streamToolCalls[$parentKey] = $value;
                }
            }
        }

        return $streamToolCalls;
    }

    /**
     * Handle the chat stream process.
     *
     * @throws \JsonException
     */
    protected function handleStream(
        \Traversable $stream,
    ): ?ChatbotHubResponseMessage
    {
        $streamContent = null;
        $streamToolCalls = [];

        /** @var \OpenAI\Responses\Chat\CreateStreamedResponse $response */
        foreach ($stream as $response) {
            $finishReason = $response->choices[0]->finishReason;

            $streamContent = $this->processStreamContent(
                $response,
                $streamContent
            );

            $streamToolCalls = $this->processStreamToolCalls(
                $response,
                $streamToolCalls
            );

            if ($finishReason === 'tool_calls'
                && ! empty($streamToolCalls)
                && $choice = CreateStreamedResponseChoice::from($streamToolCalls)
            ) {
                $toolCallResponse = $this->handleToolCall($choice);

                return $this->handleResponse($toolCallResponse);
            }

            if ($finishReason === 'stop') {
                $streamFinished = $this->streamFinished;
                $streamResponse = ChatbotHubResponseMessage::fromString(
                    $streamContent
                );

                if (is_callable($streamFinished)) {
                    $streamFinished($streamResponse);
                }

                return $streamResponse;
            }
        }

        return null;
    }
}
