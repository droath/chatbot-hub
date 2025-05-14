<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Droath\ChatbotHub\Messages\Contracts\MessageDriverAwareInterface;


trait WithMessages
{
    /**
     * @var array
     */
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
            if ($message instanceof MessageDriverAwareInterface) {
                $message->setDriver($this->driver);
            }
            if ($message instanceof Arrayable) {
                $message = $message->toArray();
            }
        }

        return array_filter($messages);
    }
}
