<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

interface ChatResourceInterface extends ResourceInterface
{
    /**
     * @return $this
     */
    public function withTools(array $tools): static;

    /**
     * @return $this
     */
    public function withMessages(array|MessageStorageInterface $messages): static;
}
