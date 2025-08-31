<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins;

use Droath\PluginManager\Discovery\NamespacePluginDiscovery;
use Droath\ChatbotHub\Attributes\AgentCoordinatorPluginMetadata;
use Droath\ChatbotHub\Plugins\Contracts\AgentCoordinatorPluginInterface;

/**
 * Define the agent coordinator plugin manager.
 */
class AgentCoordinatorPluginManager extends AgentDefaultPluginManager
{
    public function __construct(array $namespaces = [])
    {
        parent::__construct(new NamespacePluginDiscovery(
            namespaces: $this->resolveNamespaces($namespaces),
            pluginInterface: AgentCoordinatorPluginInterface::class,
            pluginMetadataAttribute: AgentCoordinatorPluginMetadata::class
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function configNamespaceKey(): string
    {
        return 'chatbot-hub.managers.agent_coordinator.namespaces';
    }
}
