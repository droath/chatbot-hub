<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\Agents;

use Droath\ChatbotHub\Plugins\Agents\AgentPlugin;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentPluginMetadata;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

#[AgentPluginMetadata(
    id: 'analysis_agent',
    label: 'Analysis Agent',
    provider: ChatbotProvider::OPENAI,
    description: 'You analyze the data provided by the research team.',
)]
class AnalysisAgent extends AgentPlugin
{
    /**
     * {@inheritDoc}
     */
    protected function systemInstruction(): ?string
    {
        return 'You are a helpful agent that analyzes the data provided by the research team.';
    }

    /**
     * {@inheritDoc}
     */
    protected function transformResponse(ChatbotHubResponseMessage|array $response): array
    {
        return [
            'analyze' => $response->message,
        ];
    }
}
