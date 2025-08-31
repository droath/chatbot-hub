<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Tests\Fixtures\Plugins\AgentCoordinator;

use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Attributes\AgentCoordinatorPluginMetadata;
use Droath\ChatbotHub\Plugins\AgentCoordinator\AgentCoordinatorPlugin;

#[AgentCoordinatorPluginMetadata(
    id: 'research_agent_coordinator',
    label: 'Research Agent Coordinator',
    provider: ChatbotProvider::OPENAI,
    strategy: AgentStrategy::SEQUENTIAL,
    agents: ['research_agent', 'analysis_agent']
)]
class ResearchAgentCoordinator extends AgentCoordinatorPlugin {}
