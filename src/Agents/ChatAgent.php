<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

/**
 * Define a chat agent class implementation.
 */
class ChatAgent implements AgentInterface
{
    /**
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $model
     * @param array|MessageStorageInterface $messages
     * @param array $tools
     */
    protected function __construct(
        protected ChatbotProvider $model,
        protected array|MessageStorageInterface $messages,
        protected array $tools
    ) {}

    /**
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $model
     * @param array|MessageStorageInterface $messages
     * @param array $tools
     *
     * @return self
     */
    public static function make(
        ChatbotProvider $model,
        array|MessageStorageInterface $messages,
        array $tools = []
    ): self
    {
        return new self($model, $messages, $tools);
    }

    /**
     * @inheritDoc
     */
    public function run(): ChatbotHubResponseMessage
    {
        return $this->createResource()->__invoke();
    }

    /**
     * @return \Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface
     */
    protected function createResource(): ChatResourceInterface
    {
        return ChatbotHub::chat($this->model)
            ->withTools($this->tools)
            ->withMessages($this->messages);
    }
}
