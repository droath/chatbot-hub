<?php

declare(strict_types=1);

use Droath\ChatbotHub\ChatbotHubClient;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Anthropic\Contracts\ClientContract;

describe('ChatbotHubClient Claude Integration', function () {
    beforeEach(function () {
        // Mock the config
        config([
            'chatbot-hub.claude.api_key' => 'test-api-key',
            'chatbot-hub.claude.base_url' => 'https://api.anthropic.com',
        ]);
    });

    test('can create claude driver', function () {
        $hubClient = new ChatbotHubClient(app());

        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        expect($driver)->toBeInstanceOf(Claude::class);
    });

    test('claude driver has anthropic client', function () {
        $hubClient = new ChatbotHubClient(app());

        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        expect($driver->client())->toBeInstanceOf(ClientContract::class);
    });

    test('claude driver uses configured api key', function () {
        config(['chatbot-hub.claude.api_key' => 'custom-key']);

        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        expect($driver)->toBeInstanceOf(Claude::class)
            ->and($driver->client())->toBeInstanceOf(ClientContract::class);
    });

    test('claude is available as provider option', function () {
        $providers = ChatbotProvider::cases();

        $claudeProvider = collect($providers)->first(fn ($provider) => $provider === ChatbotProvider::CLAUDE);

        expect($claudeProvider)->not->toBeNull()
            ->and($claudeProvider->value)->toBe('claude');
    });
});
