<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins;

use Droath\ChatbotHub\Attributes\AgentPluginMetadata;
use Droath\ChatbotHub\Plugins\Contracts\AgentPluginInterface;
use Droath\PluginManager\Discovery\NamespacePluginDiscovery;

class AgentPluginManager extends AgentDefaultPluginManager
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $namespaces = [])
    {
        parent::__construct(new NamespacePluginDiscovery(
            namespaces: $this->resolveNamespaces($namespaces),
            pluginInterface: AgentPluginInterface::class,
            pluginMetadataAttribute: AgentPluginMetadata::class
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function configNamespaceKey(): string
    {
        return 'chatbot-hub.managers.agent.namespaces';
    }
}
