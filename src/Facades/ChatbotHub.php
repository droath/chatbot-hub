<?php

namespace Droath\ChatbotHub\Facades;

use Illuminate\Support\Facades\Facade;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

/**
 * @mixin \Droath\ChatbotHub\ChatbotHub
 * @mixin \Droath\ChatbotHub\Testing\ChatbotHubFake
 *
 * @see \Droath\ChatbotHub\ChatbotHub
 */
class ChatbotHub extends Facade
{
    public static function fake(
        ?\Closure $responseCallback = null,
        ?\Closure $resourceCallback = null
    ) {
        if (is_null($responseCallback)) {
            $responseCallback = function () {
                return ChatbotHubResponseMessage::fromString(
                    'This is a fake response.'
                );
            };
        }

        return tap(
            static::getFacadeRoot(),
            function ($fake) use ($responseCallback, $resourceCallback) {
                static::swap($fake->fake($responseCallback, $resourceCallback));
            }
        );
    }

    protected static function getFacadeAccessor(): string
    {
        return \Droath\ChatbotHub\ChatbotHub::class;
    }
}
