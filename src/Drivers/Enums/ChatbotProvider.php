<?php

namespace Droath\ChatbotHub\Drivers\Enums;

enum ChatbotProvider: string
{
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';

    public function options(): array
    {
        return [
            self::OPENAI->value => 'OpenAi',
            self::ANTHROPIC->value => 'Anthropic',
        ];
    }
}
