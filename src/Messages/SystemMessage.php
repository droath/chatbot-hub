<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Messages\Concerns\ViewSupport;

/**
 * Define the system message value object.
 */
final class SystemMessage extends MessageBase
{
    use ViewSupport;

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'role' => ChatbotRoles::SYSTEM->value,
            'content' => $this->content,
        ];
    }
}
