<?php

namespace Droath\ChatbotHub;

use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Facades\ChatbotHubClient;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

/**
 * Define the chatbot hub class.
 */
class ChatbotHub
{
    /**
     * Interact with the chatbot hub chat resource.
     */
    public function chat(ChatbotProvider $provider): ChatResourceInterface
    {
        /** @var \Droath\ChatbotHub\Drivers\Openai $driver */
        $driver = ChatbotHubClient::driver($provider->value);

        if (! $driver instanceof HasChatInterface) {
            throw new \RuntimeException(
                'The driver does not support the chat resource.'
            );
        }

        return $driver->chat();
    }

    public function embeddings(ChatbotProvider $provider): void {}
}
