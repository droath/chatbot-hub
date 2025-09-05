<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources;

use Anthropic\Contracts\ClientContract;
use Anthropic\Responses\Messages\CreateResponse;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Resources\Concerns\WithMessages;
use Droath\ChatbotHub\Resources\Concerns\WithResponseFormat;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Illuminate\Support\Facades\Log;

/**
 * Define the Claude chat resource.
 */
class ClaudeChatResource implements ChatResourceInterface, HasMessagesInterface, HasResponseFormatInterface
{
    use WithMessages;
    use WithResponseFormat;

    protected string $model = Claude::DEFAULT_MODEL;

    public function __construct(
        protected ClientContract $client
    ) {}

    public function __invoke(): ?ChatbotHubResponseMessage
    {
        return $this->handleResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function withModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    protected function handleResponse(): ?ChatbotHubResponseMessage
    {
        try {
            $parameters = $this->resourceParameters();

            // Validate that we have messages to send
            if (empty($parameters['messages'])) {
                Log::warning('Claude API: No messages provided for request');

                return null;
            }

            $response = $this->client->messages()->create($parameters);

            return $this->processResponse($response);

        } catch (\Throwable $exception) {
            return $this->handleException($exception);
        }
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

        return array_filter($parameters);
    }
}
