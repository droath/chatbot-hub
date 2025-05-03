<?php

namespace Droath\ChatbotHub;

use Droath\ChatbotHub\Facades\ChatbotHubClient;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasEmbeddingInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface;

/**
 * Define the chatbot hub class.
 */
class ChatbotHub
{
    /**
     * Interact with the chatbot hub chat resource.
     *
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $provider
     *
     * @return \Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface
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

    /**
     * Interact with the chatbot hub embeddings resource.
     *
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $provider
     *
     * @return \Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface
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
