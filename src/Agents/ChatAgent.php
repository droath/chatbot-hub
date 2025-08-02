<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;

/**
 * Define a chat agent class implementation.
 */
class ChatAgent implements ChatAgentInterface
{
    /** @var \Droath\ChatbotHub\Resources\Contracts\ResourceInterface */
    protected ResourceInterface $resource;

    /**
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $provider
     * @param array $messages
     * @param array $tools
     * @param string|null $model
     * @param array $responseFormat
     */
    protected function __construct(
        protected ChatbotProvider $provider,
        protected array $messages,
        protected array $tools,
        protected ?string $model,
        protected array $responseFormat
    )
    {
        $this->resource = ChatbotHub::chat($this->provider);
    }

    /**
     * {@inheritDoc}
     */
    public static function make(
        ChatbotProvider $provider,
        array $messages,
        array $tools = [],
        string $model = null,
        array $responseFormat = []
    ): self
    {
        return new self($provider, $messages, $tools, $model, $responseFormat);
    }

    /**
     * {@inheritDoc}
     */
    public function setResourceInstance(ResourceInterface $resource): static
    {
        $this->resource = $resource;

        return $this;
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
     * @return \Droath\ChatbotHub\Resources\Contracts\ResourceInterface
     */
    protected function createResource(): ResourceInterface
    {
        if (! empty($this->model)) {
            $this->resource->withModel($this->model);
        }

        if ($this->resource instanceof HasToolsInterface) {
            $this->resource->withTools($this->tools);
        }

        if ($this->resource instanceof HasMessagesInterface) {
            $this->resource->withMessages($this->messages);
        }

        if ($this->resource instanceof HasResponseFormatInterface) {
            $this->resource->withResponseFormat($this->responseFormat);
        }

        return $this->resource;
    }
}
