<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources;

use Psr\Http\Message\ResponseInterface;
use Droath\ChatbotHub\Drivers\Perplexity;
use SoftCreatR\PerplexityAI\PerplexityAI;
use Droath\ChatbotHub\Resources\Concerns\WithMessages;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

/**
 * Define the Perplexity chat resource.
 */
class PerplexityChatResource implements ChatResourceInterface, HasMessagesInterface
{
    use WithMessages;

    protected string $model = Perplexity::DEFAULT_MODEL;

    public function __construct(
        protected PerplexityAI $client,
        protected DriverInterface $driver
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
        $response = $this->client->createChatCompletion([],
            $this->resourceParameters()
        );

        if ($response->getStatusCode() === 200) {
            return ChatbotHubResponseMessage::fromArray(
                'choices.0.message.content',
                $this->formatJsonFromResponse(
                    $response
                )
            );
        }

        return null;
    }

    /**
     * Define the chat resource parameters.
     */
    protected function resourceParameters(): array
    {
        return array_filter([
            'model' => $this->model,
            'messages' => $this->resolveMessages(),
        ]);
    }

    protected function formatJsonFromResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();

        try {
            return json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return [];
        }
    }
}
