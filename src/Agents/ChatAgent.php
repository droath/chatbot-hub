<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;

/**
 * Define a chat agent class implementation.
 */
class ChatAgent implements ChatAgentInterface
{
    protected function __construct(
        protected ChatbotProvider $provider,
        protected array $messages,
        protected array $tools,
        protected array $responseFormat
    ) {}

    /**
     * {@inheritDoc}
     */
    public static function make(
        ChatbotProvider $provider,
        array $messages,
        array $tools = [],
        array $responseFormat = []
    ): self
    {
        return new self($provider, $messages, $tools, $responseFormat);
    }

    /**
     * {@inheritDoc}
     */
    public function addTool(string $tool): static
    {
        $this->tools[] = $tool;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addTools(array $tools): static
    {
        foreach ($tools as $tool) {
            $this->addTool($tool);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMessage(UserMessage $message): static
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMessages(array $messages): static
    {
        foreach ($messages as $message) {
            if (! $message instanceof UserMessage) {
                $message = UserMessage::make($message);
            }
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addResponseFormat(array $responseFormat): static
    {
        $this->responseFormat = $responseFormat;

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
        $resource = ChatbotHub::chat($this->provider);

        if ($resource instanceof HasToolsInterface) {
            $resource->withTools($this->tools);
        }

        if ($resource instanceof HasMessagesInterface) {
            $resource->withMessages($this->messages);
        }

        if ($resource instanceof HasResponseFormatInterface) {
            $resource->withResponseFormat($this->responseFormat);
        }

        return $resource;
    }
}
