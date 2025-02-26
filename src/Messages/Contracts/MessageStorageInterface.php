<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\AssistantMessage;

/**
 * Define the message storage interface.
 */
interface MessageStorageInterface extends Arrayable
{
    /**
     * Get the message object.
     */
    public function get(int $index): UserMessage|SystemMessage;

    /**
     * Set the message object.
     *
     *
     * @return $this
     */
    public function set(UserMessage|SystemMessage|AssistantMessage $message): static;

    /**
     * Remove the message object.
     *
     *
     * @return $this
     */
    public function remove(int $index): static;

    /**
     * Save the messages in memory or persistent storage.
     */
    public function save(): void;

    /**
     * Delete the messages in memory or persistent storage.
     */
    public function delete(): void;
}
