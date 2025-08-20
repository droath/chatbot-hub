<?php

use Droath\ChatbotHub\Agents\Agent;
use Droath\ChatbotHub\Agents\AgentCoordinator;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Testing\Support\ResourceResponsesHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Testing\ClientFake;

uses(RefreshDatabase::class);
uses(ResourceResponsesHelper::class);

test('parallel agent coordinator', closure: function () {
    /** @phpstan-ignore-next-line */
    $contentResponse = $this->createFakeJsonResponse([
        'title' => 'Apple Stock Price',
        'content' => 'The current stock price of Apple is $150.',
        'tags' => ['finance', 'stock'],
    ]);
    /** @phpstan-ignore-next-line */
    $metadataResponse = $this->createFakeJsonResponse([
        'assets' => [
            [
                'ticker' => 'AAPL',
                'sector' => 'stock',
                'open' => '175.00',
                'close' => '185.00',
                'high' => '195.00',
                'low' => '150.00',
            ],
        ],
    ]);

    ChatbotHub::fake(resourceCallback: function () use ($contentResponse, $metadataResponse) {
        $client = (new ClientFake([$contentResponse, $metadataResponse]));

        return (new \Droath\ChatbotHub\Drivers\Openai($client))->responses();
    });

    $resource = ChatbotHub::responses(ChatbotProvider::OPENAI);

    $response = AgentCoordinator::make(
        'This is the website source content.',
        [
            Agent::make()->setSystemPrompt('Refine the website content.'),
            Agent::make()->setSystemPrompt('Extract the website metadata.'),
        ],
        strategy: AgentStrategy::PARALLEL
    )->run($resource);

    $agents = $response->getAgents();
    $responses = $response->getResponses();

    /** @var \Droath\ChatbotHub\Agents\Agent $agent1 */
    /** @var \Droath\ChatbotHub\Agents\Agent $agent2 */
    [$agent1, $agent2] = $agents->all();

    expect($agent1->getInputs()[0])
        ->content->toBe('Refine the website content.')
        ->and($agent1->getInputs()[1])
        ->content->toBe('This is the website source content.')
        ->and($agent2->getInputs()[0])
        ->content->toBe('Extract the website metadata.')
        ->and($agent2->getInputs()[1])
        ->content->toBe('This is the website source content.')
        ->and($responses)
        ->toHaveCount(2)
        ->get(0)->message->json()->toEqual([
            'title' => 'Apple Stock Price',
            'content' => 'The current stock price of Apple is $150.',
            'tags' => ['finance', 'stock'],
        ])
        ->get(1)->message->json()->toEqual([
            'assets' => [
                [
                    'ticker' => 'AAPL',
                    'sector' => 'stock',
                    'open' => '175.00',
                    'close' => '185.00',
                    'high' => '195.00',
                    'low' => '150.00',
                ],
            ],
        ]);
});

test('sequential agent coordinator', closure: function () {
    /** @phpstan-ignore-next-line */
    $contentResponse = $this->createFakeTextResponse(
        'This is the LLM content response'
    );
    /** @phpstan-ignore-next-line */
    $metadataResponse = $this->createFakeTextResponse(
        'This is the LLM metadata response'
    );

    ChatbotHub::fake(resourceCallback: function () use ($contentResponse, $metadataResponse) {
        $client = (new ClientFake([$contentResponse, $metadataResponse]));

        return (new \Droath\ChatbotHub\Drivers\Openai($client))->responses();
    });

    $resource = ChatbotHub::responses(ChatbotProvider::OPENAI);

    $response = AgentCoordinator::make(
        'This is the website source content.',
        [
            Agent::make()->setSystemPrompt('Create a website content.'),
            Agent::make()->setSystemPrompt('Enhance the website content for SEO.'),
        ],
    )->run($resource);

    $agents = $response->getAgents();
    $responses = $response->getResponses();

    /** @var \Droath\ChatbotHub\Agents\Agent $agent1 */
    /** @var \Droath\ChatbotHub\Agents\Agent $agent2 */
    [$agent1, $agent2] = $agents->all();

    expect($agent1->getInputs()[0])
        ->content->toBe('Create a website content.')
        ->and($agent1->getInputs()[1])
        ->content->toBe('This is the website source content.')
        ->and($agent2->getInputs()[0])
        ->content->toBe('Enhance the website content for SEO.')
        ->and($agent2->getInputs()[1])
        ->content->toBe('This is the LLM content response')
        ->and($responses)->toHaveCount(1)
        ->get(0)->message->toBe('This is the LLM metadata response');
});
