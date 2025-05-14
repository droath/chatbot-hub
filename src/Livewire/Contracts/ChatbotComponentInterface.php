<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Livewire\Contracts;

interface ChatbotComponentInterface
{
    /**
     * Send the user's message.
     *
     * @return void
     */
    public function sendMessage(): void;

    /**
     * Clear the chatbot messages.
     *
     * @return void
     */
    public function clearMessages(): void;

    /**
     * Respond to the user message from the chatbot resource.
     *
     * @return void
     */
    public function respondToMessage(): void;
}
