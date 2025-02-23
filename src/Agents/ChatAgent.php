<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

/**
 * Define a chat agent class implementation.
 */
class ChatAgent implements ChatAgentInterface
{
    /**
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $model
     * @param array|\Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface $messages
     * @param array $tools
     */
    protected function __construct(
        protected ChatbotProvider $model,
        protected array|MessageStorageInterface $messages,
        protected array $tools
    ) {}

    /**
     * @inheritDoc
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
    public function addTool(string $tool): static
    {
        $this->tools[] = $tool;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTools(array $tools): static
    {
        foreach ($tools as $tool) {
            $this->addTool($tool);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMessage(UserMessage $message): static
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @inheritDoc}
     */
    public function addMessages(array $messages): static
    {
        foreach ($messages as $message) {
            if (! $message instanceof UserMessage) {
                continue;
            }
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
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
