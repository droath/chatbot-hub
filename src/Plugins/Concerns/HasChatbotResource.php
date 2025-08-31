<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Concerns;

use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;

trait HasChatbotResource
{
    /**
     * Get the chatbot resource instance.
     */
    protected function resource(): ResourceInterface
    {
        return ChatbotHub::responses($this->provider());
    }

    /**
     * Get the chatbot resource provider.
     */
    protected function provider(): ChatbotProvider
    {
        return $this->pluginDefinition['provider'] ?? ChatbotProvider::OPENAI;
    }
}
