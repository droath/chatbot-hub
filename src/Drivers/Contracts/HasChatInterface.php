<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

/**
 * Define the chat interface.
 */
interface HasChatInterface
{
    /**
     * Define the chat resource.
     */
    public function chat(): ChatResourceInterface;
}
