<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Enums;

use Droath\ChatbotHub\Messages\MessageBase;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\AssistantMessage;

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
     * @param array $values
     *
     * @return \Droath\ChatbotHub\Messages\MessageBase
     * @throws \Exception
     */
    public static function createMessageFrom(
        string $role,
        array $values = []
    ): MessageBase
    {
        $role = self::tryFrom($role);

        return match ($role) {
            self::USER => UserMessage::fromValue($values),
            self::SYSTEM => SystemMessage::fromValue($values),
            self::ASSISTANT => AssistantMessage::fromValue($values),
            default => throw new \Exception('Unexpected match value'),
        };
    }

}
