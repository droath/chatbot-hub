<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

interface ChatAgentInterface extends AgentInterface
{
    /**
     * Create the chat agent instance.
     *
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $provider
     * @param array|\Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface $messages
     * @param array $tools
     *
     * @return self
     */
    public static function make(
        ChatbotProvider $provider,
        array|MessageStorageInterface $messages,
        array $tools = []
    ): self;

    /**
     * Add tool to the chat agent instance.
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
     * @param array $responseFormat
     *
     * @return $this
     */
    public function addResponseFormat(array $responseFormat): static;
}
