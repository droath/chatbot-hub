<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Enums;

/**
 * Define the standard chatbot roles.
 */
enum ChatbotRoles: string
{
    case TOOL = 'tool';
    case USER = 'user';
    case SYSTEM = 'system';
    case ASSISTANT = 'assistant';
}
