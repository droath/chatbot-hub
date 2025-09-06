<?php

declare(strict_types=1);

use Droath\ChatbotHub\ChatbotHubClient;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Tools\ToolProperty;

/**
 * Functional test for Claude integration with a real API key.
 *
 * This test requires ANTHROPIC_API_KEY environment variable to be set.
 *
 * Note: This test makes real API calls and should be run sparingly.
 */
describe('Claude Integration Functional Test', function () {
    test('can perform basic chat with real API', function () {
        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE);

        expect($driver)->toBeInstanceOf(Claude::class);

        $chat = $driver->chat();

        $userMessage = UserMessage::make(
            'Hello! Please respond with exactly "Test successful" if you can see this message.'
        );
        $userMessage->setDriver($driver);

        $chat->withMessages([$userMessage]);

        $response = $chat();

        expect($response)->not->toBeNull()
            ->and($response->message)->toBeString()
            ->and($response->message)->toContain('Test successful');
    });

    test('can handle system messages with real API', function () {
        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE);

        $systemMessage = SystemMessage::make('You are a helpful assistant. Always start your response with "SYSTEM:"');

        $userMessage = UserMessage::make('Say hello');
        $userMessage->setDriver($driver);

        $chat = $driver->chat();
        $chat->withMessages([$systemMessage, $userMessage]);

        $response = $chat();

        expect($response)->not->toBeNull()
            ->and($response->message)->toBeString()
            ->and($response->message)->toStartWith('SYSTEM:');
    });

    test('can handle tool calling with real API', function () {
        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        $weatherTool = Tool::make('get_weather')
            ->describe('Get current weather for a location')
            ->withProperties([
                ToolProperty::make('location', 'string')
                    ->describe('The city name')
                    ->required(),
                ToolProperty::make('unit', 'string')
                    ->describe('Temperature unit')
                    ->withEnums(['celsius', 'fahrenheit']),
            ])
            ->using(function (array $arguments) {
                $unit = $arguments['unit'];
                $location = $arguments['location'];

                return "The weather in $location is sunny with 22 $unit temperature.";
            });

        $chat = $driver->chat();
        $userMessage = UserMessage::make('What is the weather like in Paris? Use celsius.');
        $userMessage->setDriver($driver);

        $chat->withMessages([$userMessage])
            ->withTools([$weatherTool]);

        $response = $chat();

        expect($response)->not->toBeNull()
            ->and($response->message)->toContain('22')
            ->and($response->message)->toContain('°C')
            ->and($response->message)->toContain('Paris')
            ->and($response->message)->toContain('sunny');
    });

    test('can handle different Claude models', function () {
        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        $models = [
            'claude-3-haiku-20240307',
            'claude-3-5-sonnet-20241022',
        ];

        foreach ($models as $model) {
            $chat = $driver->chat()->withModel($model);

            $userMessage = UserMessage::make('Say "Model test successful" and nothing else.');
            $userMessage->setDriver($driver);

            $chat->withMessages([$userMessage]);

            $response = $chat();

            expect($response)->not->toBeNull()
                ->and($response->message)->toBeString()
                ->and($response->message)->toContain('Model test successful');
        }
    });

    test('can handle longer conversations', function () {
        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        $chat = $driver->chat();

        // First message
        $userMessage1 = UserMessage::make('My name is Test User. Remember this.');
        $userMessage1->setDriver($driver);
        $chat->withMessages([$userMessage1]);

        $response1 = $chat();
        expect($response1)->not->toBeNull();

        // Second message that references the first
        $userMessage2 = UserMessage::make('What is my name?');
        $userMessage2->setDriver($driver);

        // Add both previous messages to maintain conversation history
        $chat->withMessages([
            $userMessage1,
            // Simulate assistant response (in real conversation this would be stored)
            $userMessage2,
        ]);

        $response2 = $chat();

        expect($response2)->not->toBeNull()
            ->and($response2->message)->toBeString()
            ->and($response2->message)->toContain('Test User');
    });

    test('validates configuration correctly', function () {
        $hubClient = new ChatbotHubClient(app());
        $driver = $hubClient->driver(ChatbotProvider::CLAUDE->value);

        $errors = $driver->validateConfiguration();
        expect($errors)->toBeEmpty();

        $modelErrors = $driver->validateModel('claude-3-5-sonnet-20241022');
        expect($modelErrors)->toBeEmpty();

        $invalidModelErrors = $driver->validateModel('invalid-model');
        expect($invalidModelErrors)->not->toBeEmpty();
    });
})->skip(
    (! isset($_ENV['ANTHROPIC_API_KEY'])
    || ! isset($_ENV['ANTHROPIC_FUNCTIONAL_TEST'])
    || $_ENV['ANTHROPIC_FUNCTIONAL_TEST'] !== 'true'),
    'Requires ANTHROPIC_FUNCTIONAL_TEST=true and ANTHROPIC_API_KEY environment variables'
);
