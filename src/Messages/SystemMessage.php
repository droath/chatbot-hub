<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;

/**
 * Define the system message value object.
 */
final readonly class SystemMessage extends MessageBase
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'role' => ChatbotRoles::SYSTEM->value,
            'content' => $this->content,
        ];
    }
}
