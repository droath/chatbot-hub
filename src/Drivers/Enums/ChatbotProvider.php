<?php

namespace Droath\ChatbotHub\Drivers\Enums;

enum ChatbotProvider: string
{
    case OPENAI = 'openai';
    case PERPLEXITY = 'perplexity';
    case CLAUDE = 'claude';

    public static function options(): array
    {
        return [
            self::OPENAI->value => 'OpenAI',
            self::PERPLEXITY->value => 'Perplexity',
            self::CLAUDE->value => 'Claude',
        ];
    }
}
