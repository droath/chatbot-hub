<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\Agents;

use Droath\ChatbotHub\Plugins\Agents\AgentPlugin;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentPluginMetadata;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

#[AgentPluginMetadata(
    id: 'sport_agent',
    label: 'Sport Agent',
    provider: ChatbotProvider::OPENAI,
    tools: ['get_sports_news'],
    description: 'A sports agent that fetches the latest sports news.',
)]
class SportAgent extends AgentPlugin
{
    /**
     * {@inheritDoc}
     */
    protected function systemInstruction(): ?string
    {
        return 'You are a helpful sports agent, you task to help users fetch the latest sports news.';
    }

    /**
     * {@inheritDoc}
     */
    protected function transformResponse(ChatbotHubResponseMessage|array $response): array
    {
        return [
            'sports' => $response->message,
        ];
    }
}
