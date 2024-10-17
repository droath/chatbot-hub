<?php

namespace Droath\ChatbotHub\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Droath\ChatbotHub\ChatbotHub
 */
class ChatbotHub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Droath\ChatbotHub\ChatbotHub::class;
    }
}
