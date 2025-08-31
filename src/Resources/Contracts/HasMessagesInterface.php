<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Messages\SystemMessage;

interface HasMessagesInterface
{
    /**
     * Attach messages with the LLM resource.
     *
     * @return $this
     */
    public function withMessages(array $messages): static;

    /**
     * Push a system message to the beginning of messages.
     *
     * @return $this
     */
    public function pushSystemMessage(SystemMessage|string $message): static;
}
