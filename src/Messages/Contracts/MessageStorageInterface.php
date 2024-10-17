<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages\Contracts;

use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\AssistantMessage;

/**
 * Define the message storage interface.
 */
interface MessageStorageInterface
{
    /**
     * Get the message object.
     *
     * @param int $index
     *
     * @return \Droath\ChatbotHub\Messages\UserMessage|\Droath\ChatbotHub\Messages\SystemMessage
     */
    public function get(int $index): UserMessage|SystemMessage;

    /**
     * Set the message object.
     *
     * @param \Droath\ChatbotHub\Messages\UserMessage|\Droath\ChatbotHub\Messages\SystemMessage|\Droath\ChatbotHub\Messages\AssistantMessage $message
     *
     * @return $this
     */
    public function set(UserMessage|SystemMessage|AssistantMessage $message): static;

    /**
     * Remove the message object.
     *
     * @param int $index
     *
     * @return $this
     */
    public function remove(int $index): static;

    /**
     * Save the messages in memory or persistent storage.
     *
     * @return void
     */
    public function save(): void;

    /**
     * Delete the messages in memory or persistent storage.
     *
     * @return void
     */
    public function delete(): void;
}
