<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Attributes;

use Droath\PluginManager\Attributes\PluginMetadata;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AgentPluginMetadata extends PluginMetadata
{
    /**
     * Define the attribute constructor.
     *
     * @param string $id
     *   The plugin identifier.
     * @param string $label
     *   The plugin human-readable name.
     * @param ?string $description
     *   The agent plugin description.
     * @param \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider $provider
     *   The agent LLM resource provider.
     * @param array $tools = []
     *   The agent tools on which are available.
     */
    public function __construct(
        string $id,
        string $label,
        protected ChatbotProvider $provider,
        protected array $tools = [],
        protected ?string $description = null,
    ) {
        parent::__construct($id, $label);
    }
}
