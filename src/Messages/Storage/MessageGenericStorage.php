<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages\Storage;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

/**
 * Define message generic storage.
 */
abstract class MessageGenericStorage implements MessageStorageInterface, Arrayable
{
    public function __construct(
        protected Collection|array $messages = []
    )
    {
        $this->messages = is_array($this->messages)
            ? collect($this->messages)
            : $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function get(int $index): UserMessage|SystemMessage
    {
        return $this->messages->get($index, []);
    }

    /**
     * @inheritDoc
     */
    public function set(UserMessage|SystemMessage|AssistantMessage $message): static
    {
        $this->messages->push($message);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remove(int $index): static
    {
        $this->messages->pull($index);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->messages->toArray();
    }
}
