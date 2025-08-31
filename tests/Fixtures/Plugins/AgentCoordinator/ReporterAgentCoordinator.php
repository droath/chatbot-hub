<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentCoordinator;

use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentCoordinatorPluginMetadata;
use Droath\ChatbotHub\Plugins\AgentCoordinator\AgentCoordinatorPlugin;

#[AgentCoordinatorPluginMetadata(
    id: 'reporter_agent_coordinator',
    label: 'Reporter Agent Coordinator',
    provider: ChatbotProvider::OPENAI,
    strategy: AgentStrategy::ROUTER,
    agents: ['sport_agent', 'weather_agent']
)]
class ReporterAgentCoordinator extends AgentCoordinatorPlugin {}
