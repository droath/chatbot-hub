<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins;

use Droath\ChatbotHub\Attributes\AgentWorkerPluginMetadata;
use Droath\ChatbotHub\Plugins\Contracts\ChatAgentPluginWorkerInterface;
use Droath\PluginManager\DefaultPluginManager;
use Droath\PluginManager\Discovery\NamespacePluginDiscovery;

/**
 * Define the agent worker plugin manager.
 */
class AgentWorkerPluginManager extends DefaultPluginManager
{
    public function __construct()
    {
        parent::__construct(new NamespacePluginDiscovery(
            namespaces: ['App\Plugins'],
            pluginInterface: ChatAgentPluginWorkerInterface::class,
            pluginMetadataAttribute: AgentWorkerPluginMetadata::class
        ));
    }
}
