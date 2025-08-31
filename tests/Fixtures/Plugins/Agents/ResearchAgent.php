<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\Agents;

use Droath\ChatbotHub\Plugins\Agents\AgentPlugin;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentPluginMetadata;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

#[AgentPluginMetadata(
    id: 'research_agent',
    label: 'Research Agent',
    provider: ChatbotProvider::OPENAI,
    tools: ['search_academic_articles'],
    description: 'A research agent fetches datasets from the latest academic articles.',
)]
class ResearchAgent extends AgentPlugin
{
    /**
     * {@inheritDoc}
     */
    protected function systemInstruction(): ?string
    {
        return 'You are a helpful research agent, you task to help users fetch the latest academic articles.';
    }

    /**
     * {@inheritDoc}
     */
    protected function transformResponse(ChatbotHubResponseMessage|array $response): array
    {
        return [
            'research' => $response->message,
        ];
    }
}
