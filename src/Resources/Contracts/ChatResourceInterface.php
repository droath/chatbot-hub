<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

interface ChatResourceInterface extends ResourceInterface
{
    /**
     * @param array $tools
     *
     * @return $this
     */
    public function withTools(array $tools): static;

    /**
     * @param array|\Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface $messages
     *
     * @return $this
     */
    public function withMessages(array|MessageStorageInterface $messages): static;
}
