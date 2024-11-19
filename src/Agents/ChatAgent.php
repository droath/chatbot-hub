<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

/**
 * Define a chat agent class implementation.
 */
class ChatAgent implements AgentInterface
{
    protected function __construct(
        protected ChatbotProvider $model,
        protected array|MessageStorageInterface $messages,
        protected array $tools
    ) {}

    public static function make(
        ChatbotProvider $model,
        array|MessageStorageInterface $messages,
        array $tools = []
    ): self {
        return new self($model, $messages, $tools);
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ChatbotHubResponseMessage
    {
        return $this->createResource()->__invoke();
    }

    protected function createResource(): ChatResourceInterface
    {
        return ChatbotHub::chat($this->model)
            ->withTools($this->tools)
            ->withMessages($this->messages);
    }
}
