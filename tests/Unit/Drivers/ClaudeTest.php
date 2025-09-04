<?php

declare(strict_types=1);

use Anthropic\Contracts\ClientContract;
use Anthropic\Testing\ClientFake;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Drivers\ChatbotHubDriver;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;

describe('Claude Driver', function () {
    test('can be instantiated', function () {
        $client = new ClientFake();
        $driver = new Claude($client);

        expect($driver)->toBeInstanceOf(Claude::class)
            ->and($driver)->toBeInstanceOf(ChatbotHubDriver::class);
    });

    test('implements required interfaces', function () {
        $client = new ClientFake();
        $driver = new Claude($client);

        expect($driver)->toBeInstanceOf(HasChatInterface::class);
    });

    test('has correct default model', function () {
        expect(Claude::DEFAULT_MODEL)->toBe('claude-3-5-sonnet-20241022');
    });

    it('accepts anthropic client in constructor', function () {
        $client = new ClientFake();
        $driver = new Claude($client);

        $reflection = new ReflectionClass($driver);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);

        expect($clientProperty->getValue($driver))->toBeInstanceOf(ClientContract::class);
    });
});