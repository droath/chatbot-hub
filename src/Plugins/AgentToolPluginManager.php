<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins;

use Droath\PluginManager\DefaultPluginManager;
use Droath\ChatbotHub\Attributes\AgentToolPluginMetadata;
use Droath\PluginManager\Discovery\NamespacePluginDiscovery;
use Droath\ChatbotHub\Plugins\Contracts\AgentToolPluginInterface;

class AgentToolPluginManager extends DefaultPluginManager
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(new NamespacePluginDiscovery(
            namespaces: ['App\Plugins'],
            pluginInterface: AgentToolPluginInterface::class,
            pluginMetadataAttribute: AgentToolPluginMetadata::class
        ));
    }
}
