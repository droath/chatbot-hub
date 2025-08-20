<?php

use Droath\ChatbotHub\Agents\Agent;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Testing\Support\ResourceResponsesHelper;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Tools\ToolProperty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Testing\ClientFake;

uses(RefreshDatabase::class);
uses(ResourceResponsesHelper::class);

test('agent with tool', function () {
    /** @phpstan-ignore-next-line */
    $resourceResponse = $this->createFakeTextResponse("It's 89 degrees in Denver, CO.");

    ChatbotHub::fake(resourceCallback: function () use ($resourceResponse) {
        $client = (new ClientFake([$resourceResponse]));

        return (new \Droath\ChatbotHub\Drivers\Openai($client))->responses();
    });
    $tool = Tool::make('get_weather')
        ->describe('Get the current weather in a given location')
        ->using(function () {
            return "It's 89 degrees";
        })
        ->withProperties([
            ToolProperty::make('location', 'string')
                ->describe('The city and state, e.g. San Francisco, CA')
                ->required(),
            ToolProperty::make('unit', 'string')
                ->describe('The unit of measurement to return. Can be "imperial" or "metric".')
                ->withEnums(['celsius', 'fahrenheit']),
        ]);

    $resource = ChatbotHub::responses(ChatbotProvider::OPENAI);

    $agentResponse = Agent::make()
        ->setSystemPrompt('You are a weather bot')
        ->addInput('What is the weather in Denver, CO?')
        ->addTool($tool)
        ->run($resource);

    ChatbotHub::assertResource(function (ResponsesResourceInterface $resource) {
        $resource = invade($resource);

        /** @phpstan-ignore-next-line */
        $messages = $resource->messages;
        /** @phpstan-ignore-next-line */
        $tools = $resource->resolveTools();

        expect($messages)->toHaveCount(2)
            ->and($messages[0]->content)->toBe('You are a weather bot')
            ->and($messages[0])->toBeInstanceOf(SystemMessage::class)
            ->and($messages[1]->content)->toBe('What is the weather in Denver, CO?')
            ->and($messages[1])->toBeInstanceOf(UserMessage::class)
            ->and($tools[0])->name->toEqual('get_weather')
            ->and($tools[0]['parameters']['properties'])->toHaveKeys(['location', 'unit'])
            ->and($tools[0]['parameters']['required'])->toHaveCount(1)->toContain('location');
    });

    expect($agentResponse)
        ->toBeInstanceOf(ChatbotHubResponseMessage::class)
        ->and($agentResponse->message)->toEqual("It's 89 degrees in Denver, CO.");
});
