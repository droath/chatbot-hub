<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\Agents;

use Droath\ChatbotHub\Plugins\Agents\AgentPlugin;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentPluginMetadata;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

#[AgentPluginMetadata(
    id: 'metadata_agent',
    label: 'Metadata Agent',
    provider: ChatbotProvider::OPENAI,
    description: 'A metadata agent for generating metadata based on provided content.',
)]
class MetadataAgent extends AgentPlugin
{
    /**
     * {@inheritDoc}
     */
    protected function systemInstruction(): ?string
    {
        return 'You are a metadata agent for generating metadata';
    }

    /**
     * {@inheritDoc}
     */
    protected function transformResponse(
        ChatbotHubResponseMessage|array $response
    ): ChatbotHubResponseMessage {
        return $response;
    }
}
