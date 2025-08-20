<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ChatAgentInterface
{
    /**
     * Create the chat agent instance.
     */
    public static function make(
        ChatbotProvider $provider,
        array $messages,
        array $tools = []
    ): self;

    /**
     * Add a tool to the chat agent instance.
     *
     * @return $this
     */
    public function addTool(string $tool): static;

    /**
     * Add tools to the chat agent instance.
     *
     * @return $this
     */
    public function addTools(array $tools): static;

    /**
     * Add user message instruction to the chat agent instance.
     *
     * @return $this
     */
    public function addMessage(UserMessage $message): static;

    /**
     * Add user message instructions to the chat agent instance.
     *
     * @param \Droath\ChatbotHub\Messages\UserMessage[] $messages
     *
     * @return $this
     */
    public function addMessages(array $messages): static;

    /**
     * Add the response format to the chat agent instance.
     *
     *
     * @return $this
     */
    public function addResponseFormat(array $responseFormat): static;

    /**
     * Run the agent implementation.
     */
    public function run(): ChatbotHubResponseMessage|array;

    /**
     * Set the agent resource instance.
     *
     * @return \Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface
     */
    public function setResourceInstance(ResourceInterface $resource): static;
}
