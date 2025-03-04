<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Facades;

use Illuminate\Support\Facades\Facade;

class ChatbotHubClient extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Droath\ChatbotHub\ChatbotHubClient::class;
    }
}
