<?php

declare(strict_types=1);

use Anthropic\Contracts\ClientContract;
use Anthropic\Contracts\Resources\MessagesContract;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Testing\ClientFake;
use Droath\ChatbotHub\Drivers\Claude;
use Droath\ChatbotHub\Enums\ChatbotRoles;
use Droath\ChatbotHub\Resources\ClaudeChatResource;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\AssistantMessage;
use Droath\ChatbotHub\Messages\SystemMessage;

describe('ClaudeChatResource', function () {
    test('can be instantiated', function () {
        $client = new ClientFake();
        $resource = new ClaudeChatResource($client);

        expect($resource)->toBeInstanceOf(ClaudeChatResource::class)
            ->and($resource)->toBeInstanceOf(ChatResourceInterface::class);
    });

    test('implements required interfaces', function () {
        $client = new ClientFake();
        $resource = new ClaudeChatResource($client);

        expect($resource)->toBeInstanceOf(HasMessagesInterface::class)
            ->and($resource)->toBeInstanceOf(HasResponseFormatInterface::class);
    });

    test('has correct default model', function () {
        $client = new ClientFake();
        $resource = new ClaudeChatResource($client);

        $reflection = new ReflectionClass($resource);
        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);

        expect($modelProperty->getValue($resource))->toBe(Claude::DEFAULT_MODEL);
    });

    test('can set model', function () {
        $client = new ClientFake();
        $resource = new ClaudeChatResource($client);

        $newResource = $resource->withModel('claude-3-haiku-20240307');

        $reflection = new ReflectionClass($newResource);
        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);

        expect($modelProperty->getValue($newResource))->toBe('claude-3-haiku-20240307')
            ->and($newResource)->toBe($resource); // Should return same instance
    });

    test('stores client instance correctly', function () {
        $client = new ClientFake();
        $resource = new ClaudeChatResource($client);

        $reflection = new ReflectionClass($resource);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);

        expect($clientProperty->getValue($resource))->toBe($client)
            ->and($clientProperty->getValue($resource))->toBeInstanceOf(ClientContract::class);
    });

    describe('Message Handling', function () {
        test('can handle messages', function () {
            $client = new ClientFake();
            $resource = new ClaudeChatResource($client);

            $messages = [
                UserMessage::make('Hello Claude!'),
                SystemMessage::make('You are a helpful assistant.')
            ];
            
            $resource->withMessages($messages);

            $reflection = new ReflectionClass($resource);
            $messagesProperty = $reflection->getProperty('messages');
            $messagesProperty->setAccessible(true);

            expect($messagesProperty->getValue($resource))->toHaveCount(2);
        });

        test('separates system messages from user/assistant messages', function () {
            $client = new ClientFake();
            $resource = new ClaudeChatResource($client);

            $messages = [
                SystemMessage::make('System prompt'),
                UserMessage::make('User message'),
                AssistantMessage::make('Assistant response')
            ];
            
            $resource->withMessages($messages);

            $reflection = new ReflectionClass($resource);
            $method = $reflection->getMethod('resourceParameters');
            $method->setAccessible(true);

            $parameters = $method->invoke($resource);

            expect($parameters)->toHaveKey('system')
                ->and($parameters['system'])->toBe('System prompt')
                ->and($parameters)->toHaveKey('messages')
                ->and($parameters['messages'])->toHaveCount(2)
                ->and($parameters['messages'][0]['role'])->toBe('user')
                ->and($parameters['messages'][1]['role'])->toBe('assistant');
        });

        test('handles messages without system prompt', function () {
            $client = new ClientFake();
            $resource = new ClaudeChatResource($client);

            $messages = [
                UserMessage::make('User message')
            ];
            
            $resource->withMessages($messages);

            $reflection = new ReflectionClass($resource);
            $method = $reflection->getMethod('resourceParameters');
            $method->setAccessible(true);

            $parameters = $method->invoke($resource);

            expect($parameters)->not->toHaveKey('system')
                ->and($parameters)->toHaveKey('messages')
                ->and($parameters['messages'])->toHaveCount(1);
        });

        test('includes required parameters', function () {
            $client = new ClientFake();
            $resource = new ClaudeChatResource($client);

            $messages = [
                UserMessage::make('Test message')
            ];
            
            $resource->withMessages($messages);

            $reflection = new ReflectionClass($resource);
            $method = $reflection->getMethod('resourceParameters');
            $method->setAccessible(true);

            $parameters = $method->invoke($resource);

            expect($parameters)->toHaveKey('model')
                ->and($parameters['model'])->toBe(Claude::DEFAULT_MODEL)
                ->and($parameters)->toHaveKey('max_tokens')
                ->and($parameters['max_tokens'])->toBe(4096)
                ->and($parameters)->toHaveKey('messages')
                ->and($parameters['messages'])->toBeArray();
        });
    });

    describe('Error Handling', function () {
        test('handles API failures gracefully', function () {
            $client = $this->createMock(ClientContract::class);
            $messagesContract = $this->createMock(MessagesContract::class);
            
            $client->method('messages')->willReturn($messagesContract);
            $messagesContract->method('create')->willThrowException(new \Exception('API Error'));

            $resource = new ClaudeChatResource($client);
            $resource->withMessages([UserMessage::make('Test message')]);

            $result = $resource();

            expect($result)->toBeNull();
        });

        test('handles rate limit errors specifically', function () {
            $client = $this->createMock(ClientContract::class);
            $messagesContract = $this->createMock(MessagesContract::class);
            
            $client->method('messages')->willReturn($messagesContract);
            $exception = new \Exception('Rate limit exceeded', 429);
            $messagesContract->method('create')->willThrowException($exception);

            $resource = new ClaudeChatResource($client);
            $resource->withMessages([UserMessage::make('Test message')]);

            $result = $resource();

            expect($result)->toBeNull();
        });

        test('handles authentication errors specifically', function () {
            $client = $this->createMock(ClientContract::class);
            $messagesContract = $this->createMock(MessagesContract::class);
            
            $client->method('messages')->willReturn($messagesContract);
            $exception = new \Exception('Unauthorized: invalid api key', 401);
            $messagesContract->method('create')->willThrowException($exception);

            $resource = new ClaudeChatResource($client);
            $resource->withMessages([UserMessage::make('Test message')]);

            $result = $resource();

            expect($result)->toBeNull();
        });

        test('handles quota/billing errors specifically', function () {
            $client = $this->createMock(ClientContract::class);
            $messagesContract = $this->createMock(MessagesContract::class);
            
            $client->method('messages')->willReturn($messagesContract);
            $exception = new \Exception('Quota exceeded', 403);
            $messagesContract->method('create')->willThrowException($exception);

            $resource = new ClaudeChatResource($client);
            $resource->withMessages([UserMessage::make('Test message')]);

            $result = $resource();

            expect($result)->toBeNull();
        });

        test('handles server errors specifically', function () {
            $client = $this->createMock(ClientContract::class);
            $messagesContract = $this->createMock(MessagesContract::class);
            
            $client->method('messages')->willReturn($messagesContract);
            $exception = new \Exception('Internal server error', 500);
            $messagesContract->method('create')->willThrowException($exception);

            $resource = new ClaudeChatResource($client);
            $resource->withMessages([UserMessage::make('Test message')]);

            $result = $resource();

            expect($result)->toBeNull();
        });

        test('handles validation errors specifically', function () {
            $client = $this->createMock(ClientContract::class);
            $messagesContract = $this->createMock(MessagesContract::class);
            
            $client->method('messages')->willReturn($messagesContract);
            $exception = new \Exception('Validation error: invalid parameters', 400);
            $messagesContract->method('create')->willThrowException($exception);

            $resource = new ClaudeChatResource($client);
            $resource->withMessages([UserMessage::make('Test message')]);

            $result = $resource();

            expect($result)->toBeNull();
        });


        test('handles empty messages gracefully', function () {
            $client = new ClientFake();
            $resource = new ClaudeChatResource($client);
            
            // No messages added - should return null without making API call
            $resource->withMessages([]);

            $result = $resource();

            expect($result)->toBeNull();
        });
    });
});