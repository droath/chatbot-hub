<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Facades;

use Illuminate\Support\Facades\Facade;

class ChatbotHubAgent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Droath\ChatbotHub\ChatbotHubAgent::class;
    }
}
