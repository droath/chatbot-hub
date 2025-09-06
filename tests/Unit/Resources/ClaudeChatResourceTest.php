<?php

declare(strict_types=1);

use Anthropic\Testing\ClientFake;
use Anthropic\Responses\Messages\CreateResponse;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Resources\ClaudeChatResource;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasDriverInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolTransformerInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasStreamingInterface;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Tools\ToolProperty;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

describe('ClaudeChatResource', function () {
    test('can be instantiated', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        expect($resource)->toBeInstanceOf(ClaudeChatResource::class)
            ->and($resource)->toBeInstanceOf(ChatResourceInterface::class);
    });

    test('implements required interfaces', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        expect($resource)->toBeInstanceOf(HasMessagesInterface::class)
            ->and($resource)->toBeInstanceOf(HasResponseFormatInterface::class)
            ->and($resource)->toBeInstanceOf(HasDriverInterface::class)
            ->and($resource)->toBeInstanceOf(HasStreamingInterface::class)
            ->and($resource)->toBeInstanceOf(HasToolsInterface::class)
            ->and($resource)->toBeInstanceOf(HasToolTransformerInterface::class);
    });

    test('has correct default model', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        expect(invade($resource)->model)->toBe(Claude::DEFAULT_MODEL);
    });

    test('can set model', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        $newResource = $resource->withModel('claude-3-haiku-20240307');

        expect(invade($newResource)->model)->toBe('claude-3-haiku-20240307')
            ->and($newResource)->toBe($resource);
    });

    test('stores client and driver instances correctly', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        expect(invade($resource)->client)->toBe($client)
            ->and($resource->driver())->toBe($driver);
    });

    test('supports all Claude models', function (string $model) {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        expect(ClaudeChatResource::isModelSupported($model))->toBeTrue();
    })->with([
        'claude-3-5-sonnet-20241022',
        'claude-3-5-sonnet-20240620',
        'claude-3-5-haiku-20241022',
        'claude-3-opus-20240229',
        'claude-3-sonnet-20240229',
        'claude-3-haiku-20240307',
    ]);

    test('can handle tools', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        $tool = Tool::make('get_weather')
            ->describe('Get weather information for a location')
            ->withProperties([
                ToolProperty::make('location', 'string')
                    ->describe('The location to get weather for')
                    ->required(),
            ]);

        $resource->withTools([$tool]);

        expect(invade($resource)->tools)->toHaveCount(1);
    });

    test('transforms tools correctly', function () {
        $tool = Tool::make('test_tool')
            ->describe('A test tool')
            ->withProperties([
                ToolProperty::make('param', 'string')
                    ->describe('A parameter')
                    ->required(),
            ]);

        $transformed = ClaudeChatResource::transformTool($tool);

        expect($transformed)->toHaveKey('name')
            ->and($transformed['name'])->toBe('test_tool')
            ->and($transformed)->toHaveKey('description')
            ->and($transformed['description'])->toBe('A test tool')
            ->and($transformed)->toHaveKey('input_schema')
            ->and($transformed['input_schema'])->toBeArray();
    });

    test('can configure streaming', function () {
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        $streamProcess = fn (string $chunk, bool $initialized) => null;
        $streamFinished = fn (ChatbotHubResponseMessage $response) => null;

        $newResource = $resource->usingStream($streamProcess, $streamFinished);

        expect($newResource)->toBe($resource)
            ->and(invade($resource)->stream)->toBeTrue();
    });

    test('processes successful response correctly', function () {
        $client = new ClientFake([
            CreateResponse::fake([
                'id' => 'msg_123',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello, how can I help you?',
                    ],
                ],
            ]),
        ]);
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        $userMessage = UserMessage::make('Hello');
        $userMessage->setDriver($driver);
        $resource->withMessages([$userMessage]);

        $result = $resource();

        expect($result)->toBeInstanceOf(ChatbotHubResponseMessage::class)
            ->and($result->message)->toBe('Hello, how can I help you?');
    });

    test('handles empty messages gracefully', function () {
        // This test verifies that empty messages are handled without making API calls
        $client = new ClientFake();
        $driver = new Claude($client);
        $resource = new ClaudeChatResource($client, $driver);

        $resource->withMessages([]);

        // Should return null without making API call (no fake responses needed)
        $result = $resource();

        expect($result)->toBeNull();
    });
});
