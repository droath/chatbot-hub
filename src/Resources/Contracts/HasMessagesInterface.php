<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

interface HasMessagesInterface
{
    /**
     * @return $this
     */
    public function withMessages(array $messages): static;
}
