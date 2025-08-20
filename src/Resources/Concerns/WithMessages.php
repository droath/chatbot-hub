<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Concerns;

use Droath\ChatbotHub\Messages\Contracts\MessageDriverAwareInterface;
use Droath\ChatbotHub\Resources\Contracts\HasDriverInterface;
use Illuminate\Contracts\Support\Arrayable;

trait WithMessages
{
    protected array $messages;

    /**
     * {@inheritDoc}
     */
    public function withMessages(array $messages): static
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Resolve the resource messages.
     */
    protected function resolveMessages(): array
    {
        $messages = $this->messages;
        foreach ($messages as &$message) {
            if (
                $this instanceof HasDriverInterface
                && $message instanceof MessageDriverAwareInterface
                && ($driver = $this->driver())
            ) {
                $message->setDriver($driver);
            }
            if ($message instanceof Arrayable) {
                $message = $message->toArray();
            }
        }

        return array_filter($messages);
    }
}
