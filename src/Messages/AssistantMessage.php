<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;

/**
 * Define the assistant message value object.
 */
final class AssistantMessage extends MessageBase
{
    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'role' => ChatbotRoles::ASSISTANT->value,
            'content' => $this->content,
        ];
    }
}
