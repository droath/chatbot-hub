<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

trait WithMessages
{
    /**
     * @var array|\Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface
     */
    protected array|MessageStorageInterface $messages;

    /**
     * @inheritDoc
     */
    public function withMessages(array|MessageStorageInterface $messages): static
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

        if (is_array($messages)) {
            foreach ($messages as &$message) {
                if ($message instanceof Arrayable) {
                    $message = $message->toArray();
                }
            }
        }

        if ($messages instanceof MessageStorageInterface) {
            $messages = $this->messages->toArray();
        }

        return array_filter($messages);
    }
}
