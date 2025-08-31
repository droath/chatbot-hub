<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Attributes;

use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\PluginManager\Attributes\PluginMetadata;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AgentCoordinatorPluginMetadata extends PluginMetadata
{
    /**
     * Define the attribute constructor.
     *
     * @param string $id
     *   The plugin identifier.
     * @param string $label
     *   The plugin human-readable name.
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $provider
     *   The chatbot resource provider
     * @param \Droath\ChatbotHub\Agents\Enums\AgentStrategy $strategy
     *   The agent coordinator strategy.
     * @param array $agents
     *   The agent plugins on which to coordinate.
     */
    public function __construct(
        string $id,
        string $label,
        protected ChatbotProvider $provider,
        protected AgentStrategy $strategy,
        protected array $agents = []
    ) {
        parent::__construct($id, $label);
    }
}
