<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

interface HasMessagesInterface
{
    /**
     * @return $this
     */
    public function withMessages(array|MessageStorageInterface $messages): static;
}
