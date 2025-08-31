<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\Agents;

use Droath\ChatbotHub\Plugins\Agents\AgentPlugin;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentPluginMetadata;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

#[AgentPluginMetadata(
    id: 'weather_agent',
    label: 'Weather Agent',
    provider: ChatbotProvider::OPENAI,
    tools: ['get_weather', 'get_wind_speed'],
    description: 'A weather agent that fetches the latest weather information.',
)]
class WeatherAgent extends AgentPlugin
{
    /**
     * {@inheritDoc}
     */
    protected function systemInstruction(): ?string
    {
        return 'You are a helpful weather agent, which will help users with weather information.';
    }

    /**
     * {@inheritDoc}
     */
    protected function transformResponse(ChatbotHubResponseMessage|array $response): array
    {
        return [
            'weather' => $response->message,
        ];
    }
}
