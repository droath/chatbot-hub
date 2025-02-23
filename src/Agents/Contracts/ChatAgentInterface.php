<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;
use Droath\ChatbotHub\Messages\UserMessage;

interface ChatAgentInterface extends AgentInterface
{
    public static function make(
        ChatbotProvider $model,
        array|MessageStorageInterface $messages,
        array $tools = []
    ): self;

    /**
     * Add tool to the chat agent instance.
     *
     *
     * @return $this
     */
    public function addTool(string $tool): static;

    /**
     * Add tools to the chat agent instance.
     *
     *
     * @return $this
     */
    public function addTools(array $tools): static;

    /**
     * Add user message instruction to the chat agent instance.
     *
     *
     * @return $this
     */
    public function addMessage(UserMessage $message): static;

    /**
     * Add user message instructions to the chat agent instance.
     *
     * @param  \Droath\ChatbotHub\Messages\UserMessage[]  $messages
     * @return $this
     */
    public function addMessages(array $messages): static;
}
