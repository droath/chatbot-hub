<?php

namespace Droath\ChatbotHub;

use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasEmbeddingInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasResponsesInterface;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Facades\ChatbotHubClient;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface;
use Droath\ChatbotHub\Testing\ChatbotHubFake;

/**
 * Define the chatbot hub class.
 */
class ChatbotHub
{
    public function fake(
        ?\Closure $responseCallback = null,
        ?\Closure $resourceCallback = null
    ): ChatbotHubFake {
        return new ChatbotHubFake($responseCallback, $resourceCallback);
    }

    /**
     * Interact with the chatbot hub chat resource.
     */
    public function chat(ChatbotProvider $provider): ChatResourceInterface
    {
        /** @var \Droath\ChatbotHub\Drivers\Contracts\DriverInterface $driver */
        $driver = ChatbotHubClient::driver($provider->value);

        if (! $driver instanceof HasChatInterface) {
            throw new \RuntimeException(
                'The driver does not support the chat resource.'
            );
        }

        return $driver->chat();
    }

    public function responses(ChatbotProvider $provider): ResponsesResourceInterface
    {
        $driver = ChatbotHubClient::driver($provider->value);

        if (! $driver instanceof HasResponsesInterface) {
            throw new \RuntimeException(
                'The driver does not support the response resource.'
            );
        }

        return $driver->responses();
    }

    /**
     * Interact with the chatbot hub embeddings resource.
     */
    public function embeddings(ChatbotProvider $provider): EmbeddingsResourceInterface
    {
        /** @var \Droath\ChatbotHub\Drivers\Contracts\DriverInterface $driver */
        $driver = ChatbotHubClient::driver($provider->value);

        if (! $driver instanceof HasEmbeddingInterface) {
            throw new \RuntimeException(
                'The driver does not support the embeddings resource.'
            );
        }

        return $driver->embeddings();
    }
}
