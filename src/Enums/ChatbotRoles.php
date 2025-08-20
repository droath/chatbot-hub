<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Enums;

use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Messages\MessageBase;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\UserMessage;

/**
 * Define the standard chatbot roles.
 */
enum ChatbotRoles: string
{
    case TOOL = 'tool';
    case USER = 'user';
    case SYSTEM = 'system';
    case ASSISTANT = 'assistant';

    /**
     * @param \Droath\ChatbotHub\Enums\ChatbotRoles $role
     *
     * @throws \Exception
     */
    public static function createMessageFrom(
        string $role,
        array $values = []
    ): MessageBase {
        $role = self::tryFrom($role);

        return match ($role) {
            self::USER => UserMessage::fromValue($values),
            self::SYSTEM => SystemMessage::fromValue($values),
            self::ASSISTANT => AssistantMessage::fromValue($values),
            default => throw new \Exception('Unexpected match value'),
        };
    }
}
