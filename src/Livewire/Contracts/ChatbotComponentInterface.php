<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Livewire\Contracts;

interface ChatbotComponentInterface
{
    /**
     * Send the user's message.
     */
    public function sendMessage(): bool;

    /**
     * Clear the chatbot messages.
     */
    public function clearMessages(): void;

    /**
     * Respond to the user message from the chatbot resource.
     */
    public function respondToMessage(): void;
}
