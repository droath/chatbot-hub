<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentCoordinator;

use Droath\ChatbotHub\Agents\Agent;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Schemas\JsonSchema;
use Droath\ChatbotHub\Schemas\ArraySchema;
use Droath\ChatbotHub\Schemas\ObjectSchema;
use Droath\ChatbotHub\Schemas\StringSchema;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Attributes\AgentCoordinatorPluginMetadata;
use Droath\ChatbotHub\Plugins\AgentCoordinator\AgentCoordinatorPlugin;

#[AgentCoordinatorPluginMetadata(
    id: 'content_agent_coordinator',
    label: 'Content Agent Coordinator',
    provider: ChatbotProvider::OPENAI,
    strategy: AgentStrategy::PARALLEL,
    agents: ['metadata_agent']
)]
class ContentAgentCoordinatorPlugin extends AgentCoordinatorPlugin
{
    /**
     * {@inheritDoc}
     */
    public function agents(): array
    {
        return [
            Agent::make(name: 'content_agent')
                ->setSystemPrompt('You are a content agent for generating content')
                ->addInput('Generate content based on the provided content from the coordinator.')
                ->setResponseFormat(
                    (new JsonSchema('content_response'))
                        ->setSchema($this->responseFormatSchema())
                        ->toArray()
                )->transformResponseUsing(function (ChatbotHubResponseMessage $response) {
                    return $response->toArray();
                }),
        ];
    }

    protected function resource(): ResourceInterface
    {
        return ChatbotHub::responses($this->provider());
    }

    protected function responseFormatSchema(): ObjectSchema
    {
        return (new ObjectSchema())
            ->setProperties([
                new StringSchema(
                    name: 'title',
                    description: 'A generated title is based on the content.',
                    required: true
                ),
                new StringSchema(
                    name: 'content',
                    description: 'The content with only h3 headings. The content is required to be formated as Markdown.',
                    required: true,
                ),
                (new ArraySchema(
                    name: 'tags',
                    description: 'The tags are generated based on the content. There is a maximum of three tags.',
                    required: true,
                ))->setItems(['type' => 'string']),
            ]);
    }
}
